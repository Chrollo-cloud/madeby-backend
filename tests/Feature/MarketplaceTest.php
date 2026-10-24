<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Creator;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function listingData(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Safe test listing',
            'type' => 'product',
            'category_id' => Category::first()->id,
            'creator_id' => Creator::first()->id,
            'description' => 'Test listing only',
            'rating' => 4.5,
            'price' => 10000,
            'stock' => 3,
            'image' => UploadedFile::fake()->image('main.jpg'),
            'images' => [UploadedFile::fake()->image('gallery.png')],
            'tags' => 'test, sample',
        ], $overrides);
    }

    public function test_seeders_are_repeat_safe_and_relationships_resolve(): void
    {
        $this->seed();
        $this->assertDatabaseCount('listings', 10);
        $this->assertDatabaseCount('creators', 6);
        $this->assertDatabaseCount('categories', 7);
        $this->assertDatabaseCount('users', 1);
        foreach (Listing::with('creator', 'category', 'images')->get() as $listing) {
            $this->assertNotNull($listing->creator);
            $this->assertNotNull($listing->category);
            $this->assertNotEmpty($listing->images);
            $this->assertEquals($listing->id, $listing->images->first()->listing->id);
        }
    }

    public function test_public_api_separates_types_filters_searches_and_paginates(): void
    {
        $this->getJson('/api/products')->assertOk()->assertJsonCount(4, 'data')->assertJsonPath('data.0.type', 'product');
        $this->getJson('/api/commissions')->assertOk()->assertJsonCount(6, 'data')->assertJsonPath('data.0.type', 'commission');
        $this->getJson('/api/listings?search=tee&creator=maylin&category=FASHION&per_page=1')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 2)->assertJsonPath('meta.last_page', 2);
        $this->getJson('/api/products/studio-portrait-session')->assertNotFound();
        $this->getJson('/api/creators/maylin')->assertOk()->assertJsonPath('data.works', 2);
        $this->getJson('/api/creators?per_page=2&page=2')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/listings?type=invalid')->assertUnprocessable();
        $this->getJson('/api/categories')->assertOk()->assertJsonCount(7, 'data');
        $this->getJson('/api/listings/absent')->assertNotFound();
    }

    public function test_admin_and_api_reject_guests_and_non_admins(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->postJson('/api/listings', [])->assertUnauthorized();
        $this->actingAs(User::factory()->create());
        $this->get('/admin/dashboard')->assertForbidden();
        $this->postJson('/api/listings', [])->assertForbidden();
        $this->postJson('/api/creators', [])->assertForbidden();
    }

    public function test_admin_dashboard_and_web_crud_use_database(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin/dashboard')->assertOk()->assertViewHas('total', 10)->assertViewHas('products', 4)->assertViewHas('commissions', 6);
        $this->get('/admin/listings?s=Tee&type=product')->assertOk()->assertViewHas('listings', fn ($rows) => $rows->total() === 2);
        $this->get('/admin/listings/creator/'.Creator::first()->id)->assertOk();
        $this->get('/admin/listings/create')->assertOk();
        $this->post('/admin/listings', $this->listingData())->assertRedirect('/admin/listings');
        $listing = Listing::where('slug', 'safe-test-listing')->firstOrFail();
        $this->get('/admin/listings/'.$listing->id.'/edit')->assertOk();
        $this->put('/admin/listings/'.$listing->id, $this->listingData(['title' => 'Updated safe listing']))->assertRedirect('/admin/listings');
        $this->assertDatabaseHas('listings', ['id' => $listing->id, 'title' => 'Updated safe listing']);
        $this->delete('/admin/listings/'.$listing->id)->assertRedirect('/admin/listings');
        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
    }

    public function test_api_crud_upload_gallery_replacement_and_type_transition(): void
    {
        $this->actingAs($this->admin());
        $response = $this->postJson('/api/listings', $this->listingData())->assertCreated()->assertJsonPath('data.id', 'safe-test-listing');
        $listing = Listing::where('slug', 'safe-test-listing')->firstOrFail();
        $oldImage = $listing->image;
        $gallery = $listing->images->first();
        Storage::disk('public')->assertExists($oldImage);
        Storage::disk('public')->assertExists($gallery->path);
        $this->putJson('/api/listings/safe-test-listing', $this->listingData([
            'type' => 'commission', 'price_from' => 20000, 'commission_slots' => 2,
            'delivery_time' => '3 days', 'remove_images' => [$gallery->id],
        ]))->assertOk()->assertJsonPath('data.price', null)->assertJsonPath('data.stock', null)->assertJsonCount(1, 'data.gallery');
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertMissing($gallery->path);
        $listing->refresh();
        $newImage = $listing->image;
        $newGallery = $listing->images()->first()->path;
        $this->getJson('/api/commissions/safe-test-listing')->assertOk();
        $this->deleteJson('/api/listings/safe-test-listing')->assertNoContent();
        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
        $this->assertDatabaseMissing('listing_images', ['listing_id' => $listing->id]);
        Storage::disk('public')->assertMissing($newImage);
        Storage::disk('public')->assertMissing($newGallery);
    }

    public function test_validation_rejects_invalid_uploads_numbers_and_foreign_gallery_ids(): void
    {
        $this->actingAs($this->admin());
        $this->postJson('/api/listings', [])->assertUnprocessable()->assertJsonValidationErrors(['title', 'type', 'image']);
        $this->postJson('/api/listings', $this->listingData([
            'stock' => 1.5, 'creator_id' => 999999, 'image' => UploadedFile::fake()->create('bad.txt', 1),
        ]))->assertUnprocessable()->assertJsonValidationErrors(['stock', 'creator_id', 'image']);
        $first = Listing::first();
        $otherImage = Listing::where('id', '!=', $first->id)->first()->images()->first();
        $this->putJson('/api/listings/'.$first->slug, $this->listingData(['remove_images' => [$otherImage->id]]))
            ->assertUnprocessable()->assertJsonValidationErrors('remove_images.0');
    }

    public function test_sanctum_login_revocation_and_breeze_logout(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'password' => Hash::make('test-secret')]);
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])->assertUnprocessable();
        $token = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'test-secret'])
            ->assertOk()->json('token');
        $this->withToken($token)->getJson('/api/user')->assertOk()->assertJsonPath('data.id', $user->id);
        $this->withToken($token)->postJson('/api/listings', $this->listingData())->assertCreated();
        $this->withToken($token)->deleteJson('/api/listings/safe-test-listing')->assertNoContent();
        $this->withToken($token)->postJson('/api/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withHeaders(['Authorization' => ''])->actingAs($user, 'web')->post('/logout')->assertRedirect('/');
        $this->assertGuest('web');
    }

    public function test_creator_crud_and_delete_conflict_preserve_listings(): void
    {
        $this->actingAs($this->admin());
        $data = ['slug' => 'safe-creator', 'name' => 'Safe Creator', 'role' => 'Artist',
            'bio' => 'Test', 'location' => 'Test', 'followers' => '0', 'likes' => '0',
            'avatar' => UploadedFile::fake()->image('avatar.jpg')];
        $this->postJson('/api/creators', $data)->assertCreated();
        $data['name'] = 'Updated Creator';
        unset($data['avatar']);
        $this->putJson('/api/creators/safe-creator', $data)->assertOk()->assertJsonPath('data.name', 'Updated Creator');
        $this->deleteJson('/api/creators/safe-creator')->assertNoContent();
        $this->deleteJson('/api/creators/maylin')->assertStatus(409);
        $this->assertDatabaseCount('listings', 10);
    }
}
