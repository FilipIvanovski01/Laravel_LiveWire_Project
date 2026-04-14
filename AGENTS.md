# AGENT.md — Mini E-Commerce Platform

> Drop this file in your project root. Your AI coding agent (Claude Code, Cursor, Copilot, etc.) will use it to understand your project's architecture, conventions, and rules — so every suggestion it makes already follows your standards.

---

## Project Overview

**Name:** Mini E-Commerce Platform
**Stack:** Laravel 12.x, Livewire 3 (Volt), Tailwind CSS 4.x, Alpine.js 3.x, MySQL
**Architecture:** Domain-Driven Design (DDD)
**PHP Version:** 8.2+
**Testing:** Pest / PHPUnit

---

## Domain Map

This app has four bounded contexts (business domains):

| Domain | Responsibility | Key Models |
|---|---|---|
| **IdentityAndAccess** | Users, roles, authentication | `User` |
| **ProductCatalog** | Vendors, products, marketplace browsing | `Vendor`, `Product` |
| **Cart** | Shopping cart with stock validation | `Cart`, `CartItem` |
| **OrderManagement** | Checkout, orders, status tracking | `Order`, `OrderItem` |

### Key Relationships

```
User ──has one──▶ Vendor (if role = vendor)
Vendor ──has many──▶ Product
User ──has one──▶ Cart
Cart ──has many──▶ CartItem ──belongs to──▶ Product
Order ──belongs to──▶ User (buyer)
Order ──has many──▶ OrderItem ──belongs to──▶ Product
OrderItem ──belongs to──▶ Vendor (denormalised for vendor order views)
```

---

## Directory Structure

```
app/
├── Domain/
│   ├── IdentityAndAccess/
│   │   ├── Actions/              # RegisterUserAction, AssignRoleAction
│   │   ├── Enums/                # UserRole (buyer, vendor, admin)
│   │   ├── Models/               # User
│   │   └── Policies/
│   ├── ProductCatalog/
│   │   ├── Actions/              # CreateProductAction, UpdateProductAction
│   │   ├── DTOs/                 # CreateProductDTO
│   │   ├── Enums/                # ProductStatus (draft, active, archived)
│   │   ├── Models/               # Vendor, Product
│   │   ├── Policies/             # ProductPolicy
│   │   └── Services/             # MarketplaceSearchService
│   ├── Cart/
│   │   ├── Actions/              # AddToCartAction, RemoveFromCartAction
│   │   ├── Models/               # Cart, CartItem
│   │   └── Services/             # CartStockValidationService
│   └── OrderManagement/
│       ├── Actions/              # CreateOrderAction, UpdateOrderStatusAction
│       ├── DTOs/                 # CreateOrderDTO
│       ├── Enums/                # OrderStatus, PaymentMethod
│       ├── Models/               # Order, OrderItem
│       └── Services/             # CheckoutService, PaymentSimulatorService
├── Http/
│   ├── Controllers/              # Thin — delegate to Actions/Services
│   └── Middleware/               # RoleMiddleware
resources/
├── views/
│   ├── livewire/
│   │   ├── market/               # index (browse), show (product detail)
│   │   ├── cart/                 # index (cart summary)
│   │   ├── checkout/             # index (checkout flow)
│   │   ├── vendor/               # products/index, products/create, orders
│   │   └── buyer/                # orders/index, orders/show
│   ├── layouts/
│   └── components/
database/
├── migrations/
├── seeders/                      # DatabaseSeeder, VendorSeeder, ProductSeeder
└── factories/                    # One factory per model
tests/
├── Feature/                      # HTTP + Livewire component tests
└── Unit/                         # Action and Service unit tests
```

---

## Conventions & Rules

### Naming

- **Models:** Singular PascalCase → `Product`, `CartItem`, `OrderItem`
- **Tables:** Plural snake_case → `products`, `cart_items`, `order_items`
- **Actions:** Verb + Noun + "Action" → `CreateProductAction`, `AddToCartAction`
- **DTOs:** Noun + "DTO" → `CreateProductDTO`, `CreateOrderDTO`
- **Enums:** Noun → `OrderStatus`, `UserRole`, `PaymentMethod`
- **Services:** Noun + "Service" → `CheckoutService`, `PaymentSimulatorService`

### Architecture Rules

1. **Controllers are thin.** Validate the request, call an Action or Service, return a response. Zero business logic.
2. **Actions do one thing.** `CreateOrderAction` creates an order. It does not send emails or clear carts.
3. **Services orchestrate.** `CheckoutService` calls `CreateOrderAction` + `DecrementStockAction` + `ClearCartAction` inside a transaction.
4. **Models own relationships and scopes.** `Product::scopeActive()`, `Product::scopeForVendor($vendor)`.
5. **DTOs carry data between layers.** Never pass a raw `$request` into an Action.
6. **Enums for finite sets.** `OrderStatus`, `UserRole`, `PaymentMethod` — never magic strings.

### Database

- **Primary keys:** ULIDs → `$table->ulid('id')->primary()`
- **Foreign keys:** Always constrained → `$table->foreignUlid('vendor_id')->constrained()->cascadeOnDelete()`
- **Money:** Use `decimal(10, 2)` for prices. Never `float`.
- **Soft deletes:** On `products` and `orders`. Not on pivot tables like `cart_items`.
- **Indexes:** On any column used in `WHERE`, `ORDER BY`, or `JOIN`.

### Business Rules

- A user's role is stored as an enum column (`buyer`, `vendor`, `admin`). A user can be both buyer and vendor.
- Stock validation happens at two points: when adding to cart AND at checkout. Cart addition warns; checkout rejects.
- Checkout is wrapped in `DB::transaction()`. Stock decrement, order creation, and cart clearing are atomic.
- Payment simulation: orders over $999 fail. All others succeed. This is handled by `PaymentSimulatorService`.
- Order status transitions: `pending → paid → shipped → delivered`. Only forward transitions are allowed. Use `OrderStatus::canTransitionTo()`.

### Frontend / UI

- **Livewire Volt** single-file components for all interactive pages.
- **Tailwind CSS** only — no custom CSS files.
- **Alpine.js** for small client-side interactions (dropdowns, quantity steppers).

### Testing

- **Feature tests** for: cart stock validation, checkout success/failure, role-based access denial, order status transitions.
- **Unit tests** for: `CheckoutService`, `PaymentSimulatorService`, `CartStockValidationService`.
- Use `RefreshDatabase` trait. Use Factories — never manual `::create([...])`.

### Git

- Imperative mood, max 72 chars: `Add checkout flow with stock validation`
- Branch names: `feature/cart-stock-validation`, `fix/checkout-race-condition`
- One logical change per commit.

---

## Common Commands

```bash
php artisan serve                          # Start dev server
php artisan migrate:fresh --seed           # Reset DB with sample data
php artisan make:migration create_X_table  # New migration
php artisan test                           # Run all tests
php artisan test --filter=CheckoutTest     # Run specific test
./vendor/bin/pint                          # Fix code style
php artisan optimize:clear                 # Clear all caches
```

---

## When Generating Code

- Place models under `app/Domain/{Context}/Models/`.
- Always create a Factory alongside a new Model.
- Feature order: Migration → Model → Factory → Seeder → Action/DTO → Volt Component → Route → Test.
- Never `$guarded = []`. Always explicit `$fillable`.
- Always type-hint parameters and return types.
- Wrap multi-step writes in `DB::transaction()`.
- Use `readonly` DTOs with named constructor arguments.

---

## Example Prompts

Here are prompts that work well with this project's architecture:

**Scaffolding a feature:**
> "Create the Cart domain: migration for `carts` and `cart_items` tables, Eloquent models with relationships, a Factory for each, and a seeder that gives each buyer a cart with 2-3 random products."

**Building business logic:**
> "Create a `CheckoutService` in the OrderManagement domain. It should: validate all cart items have sufficient stock, create an Order with OrderItems from the cart, call `PaymentSimulatorService` (fail if total > $999), decrement stock on success, clear the cart, and return the Order. Wrap everything in a DB transaction. Throw a descriptive exception on failure."

**Writing tests:**
> "Write a Feature test for checkout. Test three scenarios: (1) successful checkout with stock decremented, (2) checkout fails when payment is rejected (order > $999), cart stays intact, (3) checkout fails when a product has insufficient stock."
