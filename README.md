## USPS Shipping Rates Integration

This project includes an integration with the USPS API to retrieve shipping rates. The implementation consists of a `ShippingController` and corresponding routes for handling shipping form display and rate calculations.

### Routes
The following routes are defined in `routes/web.php`:

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShippingController;

Route::get('/shipping-form', [ShippingController::class, 'showForm']);
Route::post('/get-usps-rates', [ShippingController::class, 'getRates'])->name('usps.getRates');