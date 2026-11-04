<x-layouts.app :title="'Edit '.$permission->name">
    <x-page-header :title="'Edit '.$permission->name" :breadcrumbs="['Permissions' => route('permissions.index'), $permission->name]" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('permissions.update', $permission) }}" novalidate>
                @csrf
                @method('PUT')
                @include('permissions.partials.form')
            </form>
        </div>
    </div>
</x-layouts.app>

