<x-layouts.app title="New Permission">
    <x-page-header title="New Permission" :breadcrumbs="['Permissions' => route('permissions.index'), 'New Permission']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('permissions.store') }}" novalidate>
                @csrf
                @include('permissions.partials.form')
            </form>
        </div>
    </div>
</x-layouts.app>

