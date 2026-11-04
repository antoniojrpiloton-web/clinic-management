<x-layouts.app title="New Role">
    <x-page-header title="New Role" :breadcrumbs="['Roles' => route('roles.index'), 'New Role']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('roles.store') }}" novalidate>
                @csrf
                @include('roles.partials.form')
            </form>
        </div>
    </div>
</x-layouts.app>

