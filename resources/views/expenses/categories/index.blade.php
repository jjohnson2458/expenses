@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Categories</h4>
    <a href="{{ url('/categories/create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Category
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;"></th>
                        <th style="width: 40px;">Color</th>
                        <th style="width: 40px;">Icon</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="sortableCategories">
                    @forelse($categories ?? [] as $category)
                    <tr data-id="{{ $category->id ?? $category['id'] }}">
                        <td class="text-muted" style="cursor: grab;">
                            <i class="bi bi-grip-vertical"></i>
                        </td>
                        <td>
                            <div class="rounded-circle" style="width: 24px; height: 24px; background: {{ $category->color ?? $category['color'] ?? '#6c757d' }};"></div>
                        </td>
                        <td>
                            @if(!empty($category->icon ?? $category['icon'] ?? ''))
                                <i class="bi bi-{{ $category->icon ?? $category['icon'] }}"></i>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $category->name ?? $category['name'] }}</td>
                        <td class="text-muted small">{{ $category->description ?? $category['description'] ?? '' }}</td>
                        <td>
                            @if(($category->active ?? $category['active'] ?? 1))
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ url('/categories/' . ($category->id ?? $category['id']) . '/edit') }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ url('/categories/' . ($category->id ?? $category['id']) . '/delete') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-tags fs-1 d-block mb-2"></i>
                            No categories yet. <a href="{{ url('/categories/create') }}">Create your first category</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function() {
    $('#sortableCategories').sortable({
        handle: '.bi-grip-vertical',
        axis: 'y',
        cursor: 'grabbing',
        placeholder: 'table-sort-placeholder',
        helper: function(e, tr) {
            var originals = tr.children();
            var helper = tr.clone();
            helper.children().each(function(index) {
                $(this).width(originals.eq(index).width());
            });
            return helper;
        },
        update: function(event, ui) {
            var order = [];
            $('#sortableCategories tr').each(function(index) {
                order.push({
                    id: $(this).data('id'),
                    sort_order: index + 1
                });
            });

            $.ajax({
                url: '{{ url("/categories/reorder") }}',
                method: 'POST',
                data: JSON.stringify({ order: order }),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function() {
                    alert('Failed to save sort order. Please try again.');
                    location.reload();
                }
            });
        }
    });
});
</script>
@endpush
