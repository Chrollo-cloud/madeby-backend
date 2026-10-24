<x-admin-layout :title="$title">
<a class="bg-[#ff6046] p-3 font-bold" href="{{route('admin.listings.create')}}">+ New listing</a>
<form class="flex gap-2 my-6">
<input name="s" value="{{request('s')}}" placeholder="Search title">
<select name="id_creator">
<option value="">All creators</option>
@foreach($creators as $creator)<option value="{{$creator->id}}" @selected(request('id_creator')==$creator->id)>{{$creator->name}}</option>
@endforeach
</select>
<select name="type">
<option value="">All types</option>
<option value="product" @selected(request('type')==='product')>Ready-made</option>
<option value="commission" @selected(request('type')==='commission')>Commissions</option>
</select>
<button>Filter</button>
</form>
<table class="w-full text-left">
<tr>
<th>Listing</th>
<th>Type</th>
<th>Category</th>
<th>Creator</th>
<th>Price</th>
<th>Actions</th>
</tr>
@foreach($listings as $listing)<tr class="border-t">
<td>
<img class="h-12 w-12 object-cover inline" src="{{Storage::url($listing->image)}}"> {{$listing->title}}</td>
<td>{{$listing->type}}</td>
<td>{{$listing->category->name}}</td>
<td>
<a class="underline" href="{{route('admin.listings.creator',$listing->creator_id)}}">{{$listing->creator->name}}</a>
</td>
<td>Rp {{number_format($listing->price ?? $listing->price_from)}}</td>
<td>
<a href="{{route('admin.listings.edit',$listing)}}">Edit</a>
<form class="inline" method="POST" action="{{route('admin.listings.destroy',$listing)}}">@csrf @method('DELETE')<button onclick="return confirm('Are you sure?')">Delete</button>
</form>
</td>
</tr>
@endforeach
</table>
@if(method_exists($listings,'links'))<div class="mt-5">{{$listings->links()}}</div>
@endif
</x-admin-layout>
