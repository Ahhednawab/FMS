<form action="{{ $route }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name"
               value="{{ old('name', $supplier->name ?? '') }}"
               class="form-control" required>
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="mb-3">
        <label>Contact</label>
        <input type="text" name="contact"
               value="{{ old('contact', $supplier->contact ?? '') }}"
               class="form-control" required>
        @error('contact') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="mb-3">
        <label>Address</label>
        <input type="text" name="address"
               value="{{ old('address', $supplier->address ?? '') }}"
               class="form-control">
        @error('address') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <button class="btn btn-success">{{ $method === 'POST' ? 'Create' : 'Update' }}</button>
</form>
