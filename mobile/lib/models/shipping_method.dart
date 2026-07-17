import 'money.dart';

/// O metodă de livrare disponibilă, cu costul deja calculat pentru coșul
/// curent al utilizatorului — forma întoarsă de
/// `GET /api/v1/checkout/shipping-methods` (Partea 12):
///
/// ```json
/// { "data": [ { "code": "flat", "label": "...", "cost": {...} }, ... ] }
/// ```
///
/// `code` e valoarea trimisă înapoi ca `shippingCode` la `POST /checkout`.
class ShippingMethod {
  final String code;
  final String label;
  final Money cost;

  const ShippingMethod({required this.code, required this.label, required this.cost});

  factory ShippingMethod.fromJson(Map<String, dynamic> json) {
    return ShippingMethod(
      code: json['code'] as String,
      label: json['label'] as String,
      cost: Money.fromJson(json['cost'] as Map<String, dynamic>),
    );
  }
}
