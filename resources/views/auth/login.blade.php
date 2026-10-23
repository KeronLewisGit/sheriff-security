<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>Sign in · Sheriff staff</title>
    <meta name="robots" content="noindex">
</head>
<body class="admin relative isolate grid min-h-screen place-items-center bg-brand-950 px-4 py-10">
    <div class="graticule absolute inset-0 -z-10" aria-hidden="true"></div>
    <main class="w-full max-w-sm">
        <a href="{{ route('home') }}" class="mx-auto mb-8 flex w-max"><x-logo tone="light" /></a>
        <form method="POST" action="{{ route('login') }}" class="a-card space-y-5 p-6 sm:p-8">
            @csrf
            <div>
                <h1 class="font-display text-xl font-semibold">Staff dashboard</h1>
                <p class="mt-1 text-sm text-ink-3">Business development, HR and administrators</p>
            </div>
            <div>
                <label class="a-label" for="email">Email</label>
                <input class="a-input w-full" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                <x-error for="email" />
            </div>
            <div>
                <label class="a-label" for="password">Password</label>
                <input class="a-input w-full" id="password" name="password" type="password" required autocomplete="current-password">
            </div>
            <label class="flex min-h-11 items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="h-4 w-4">Keep me signed in on this device</label>
            <button class="a-btn a-btn-primary w-full">Sign in</button>
        </form>
    </main>
</body>
</html>
