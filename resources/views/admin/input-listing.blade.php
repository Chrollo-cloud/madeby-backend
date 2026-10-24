<x-admin-layout :title="$title">
@if($errors->any())<ul role="alert">
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach
</ul>
@endif

<form method="POST" enctype="multipart/form-data" action="{{isset($listing)?route('admin.listings.update',$listing):route('admin.listings.store')}}" class="grid gap-3 max-w-2xl">@csrf 
@isset($listing)@method('PUT')
@endisset
<input name="title" value="{{old('title',$listing->title??'')}}" placeholder="Title">@error('title')<small>{{$message}}</small>@enderror <label>
<input type="radio" name="type" value="product" @checked(old('type',$listing->type??'product')==='product')> Product</label>
<label>
<input type="radio" name="type" value="commission" @checked(old('type',$listing->type??'')==='commission')> Commission</label>
<select name="category_id">
@foreach($categories as $category)<option value="{{$category->id}}" @selected(old('category_id',$listing->category_id??'')==$category->id)>{{$category->name}}</option>
@endforeach
</select>
<select name="creator_id">
@foreach($creators as $creator)<option value="{{$creator->id}}" @selected(old('creator_id',$listing->creator_id??'')==$creator->id)>{{$creator->name}}</option>
@endforeach
</select>
<input name="price" value="{{old('price',$listing->price??'')}}" placeholder="Price">
<input name="price_from" value="{{old('price_from',$listing->price_from??'')}}" placeholder="Price from">
<input name="stock" value="{{old('stock',$listing->stock??'')}}" placeholder="Stock">
<input name="commission_slots" value="{{old('commission_slots',$listing->commission_slots??'')}}" placeholder="Commission slots">
<input name="delivery_time" value="{{old('delivery_time',$listing->delivery_time??'')}}" placeholder="Delivery time">
<input name="rating" value="{{old('rating',$listing->rating??'0')}}" placeholder="Rating">
<input name="tags" value="{{old('tags',isset($listing)?implode(', ',$listing->tags??[]):'')}}" placeholder="tags, comma separated">
<textarea name="description" placeholder="Description">{{old('description',$listing->description??'')}}</textarea>
<label>Main image <input type="file" name="image">
</label>
<label>Gallery <input type="file" name="images[]" multiple>
</label>
@isset($listing)
<fieldset>
<legend>Existing gallery</legend>

@foreach($listing->images as $galleryImage)
<label>
<img src="{{ asset('storage/'.$galleryImage->path) }}" alt="Gallery image" width="100">
<input type="checkbox" name="remove_images[]" value="{{ $galleryImage->id }}"> Remove image</label>

@endforeach

</fieldset>

@endisset

<button class="bg-[#173d2d] text-white p-3">Save listing</button>
</form>
</x-admin-layout>
