@php use App\Enums\Role; @endphp
<x-layouts.admin title="Users & roles">
    <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
        <div class="a-card overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Name</th><th>Role</th><th></th></tr></thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr>
                            <td class="font-bold">{{ $u->name }}<span class="block text-sm font-normal text-ink-3">{{ $u->email }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.update', $u) }}" class="flex gap-2">@csrf @method('PATCH')
                                    <label class="sr-only" for="role-{{ $u->id }}">Role for {{ $u->name }}</label>
                                    <select id="role-{{ $u->id }}" name="role" class="a-input">@foreach (Role::cases() as $r)<option value="{{ $r->value }}" @selected($u->role === $r)>{{ $r->label() }}</option>@endforeach</select>
                                    <button class="a-btn a-btn-ghost">Save</button>
                                </form>
                            </td>
                            <td class="r">@unless ($u->is(auth()->user()))<form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Remove this account?')">@csrf @method('DELETE')<button class="a-btn a-btn-danger">Remove</button></form>@endunless</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="a-card h-max space-y-4 p-5">
            @csrf
            <h2 class="font-bold">Add a staff account</h2>
            <div><label class="a-label" for="name">Name</label><input id="name" name="name" class="a-input w-full" value="{{ old('name') }}" required></div>
            <div><label class="a-label" for="email">Email</label><input id="email" name="email" type="email" class="a-input w-full" value="{{ old('email') }}" required></div>
            <div><label class="a-label" for="role">Role</label><select id="role" name="role" class="a-input w-full">@foreach (Role::cases() as $r)<option value="{{ $r->value }}">{{ $r->label() }}</option>@endforeach</select></div>
            <div><label class="a-label" for="password">Temporary password <span class="font-normal text-ink-3">(10+ characters)</span></label><input id="password" name="password" type="password" minlength="10" class="a-input w-full" required autocomplete="new-password"></div>
            <button class="a-btn a-btn-primary w-full">Create account</button>
            <div class="text-sm text-ink-3"><p><strong>Business development</strong>: proposals, vendor packs, compliance documents, alarm tickets, messages.</p><p class="mt-1"><strong>Human resources</strong>: jobs and applications.</p><p class="mt-1"><strong>Administrator</strong>: everything, plus users and settings.</p></div>
        </form>
    </div>
</x-layouts.admin>
