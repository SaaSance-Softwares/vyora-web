# Vyora Project Rules and Knowledge Base

This file contains strict guidelines, system context, and accumulated knowledge about the Vyora project architecture that MUST be adhered to whenever making future changes. 

## Authentication Architecture Rules (CRITICAL)

The authentication structure for this application uses a hybrid approach (React/Inertia Frontend + Laravel API/Web). Failure to respect these rules will break the login and registration flow.

1. **Session & Cookie Dependencies on Auth API Routes**
   - The `/api/login` and `/api/register` endpoints are defined in `routes/api.php` BUT they heavily rely on the `web` session store to authenticate users properly across the full web stack.
   - **Rule:** The `routes/api.php` definitions for `/login` and `/register` MUST always be wrapped with the following middleware explicitly:
     ```php
     Route::middleware([
         \Illuminate\Session\Middleware\StartSession::class,
         \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
         \Illuminate\Cookie\Middleware\EncryptCookies::class
     ])->group(function () {
         Route::post('/register', [AuthController::class, 'register']);
         Route::post('/login', [AuthController::class, 'login']);
     });
     ```
   - **Why:** If these middlewares are removed, Laravel cannot generate the session cookie, and `session()->regenerate()` inside the `AuthController` will crash with a `Session store not set on request` runtime error.

2. **Session Regeneration inside AuthController**
   - Inside `app/Http/Controllers/Api/AuthController.php`, any successful authentication (Login or Registration) must explicitly bind the web guard and regenerate the session:
     ```php
     Auth::guard('web')->login($user);
     $request->session()->regenerate();
     ```
   - **Why:** This ensures that both the API token is returned for future frontend API requests, AND the encrypted web session cookie is sent to the browser so that Inertia/web routes instantly recognize the authenticated user.

3. **React/Inertia Imports**
   - When modifying frontend components (like `LoginForm.tsx` or `RegisterForm.tsx`), any usage of `usePage()` to access Inertia props must be explicitly imported:
     ```typescript
     import { usePage } from '@inertiajs/react';
     ```
   - **Why:** Missing this import breaks the Vite compiler and entirely crashes the React DOM rendering, resulting in a blank page without throwing explicit errors in the standard console view.

## WhatsApp Integration
- Template variables must be scoped specifically to their target components (e.g. `HEADER` vs `BODY`) during resolution in `WhatsAppService.php` to avoid global replacement collisions (e.g. `{{1}}` in Header being overwritten by `{{1}}` in Body).
- When resolving the `customer_name` variable for an `Order` object, always fallback to the attached `$order->user->name` if the order itself lacks a direct `customer_name` string.
