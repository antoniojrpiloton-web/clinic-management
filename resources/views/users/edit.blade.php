<x-layouts.app :title="'Edit '.$user->name">
    <x-page-header :title="'Edit '.$user->name" :breadcrumbs="['Users' => route('users.index'), $user->name]" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}" novalidate>
                @csrf
                @method('PUT')
                @include('users.partials.form')
            </form>
        </div>
    </div>
</x-layouts.app>

