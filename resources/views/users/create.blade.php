<x-layouts.app title="New User">
    <x-page-header title="New User" :breadcrumbs="['Users' => route('users.index'), 'New User']" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}" novalidate>
                @csrf
                @include('users.partials.form')
            </form>
        </div>
    </div>
</x-layouts.app>

