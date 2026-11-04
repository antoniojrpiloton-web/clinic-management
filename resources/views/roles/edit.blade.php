<x-layouts.app :title="'Edit '.$role->name">
    <x-page-header :title="'Edit '.$role->name" :breadcrumbs="['Roles' => route('roles.index'), $role->name]" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('roles.update', $role) }}" novalidate>
                @csrf
                @method('PUT')
                @include('roles.partials.form')
            </form>
        </div>
    </div>
</x-layouts.app>

