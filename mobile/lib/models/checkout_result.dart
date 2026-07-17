import 'order.dart';

/// Datele unei redirecționări de plată online (Netopia/PayU, Partea 11) —
/// `null` pentru ramburs sau pentru comenzile plasate cu gateway-ul „mock".
/// Un ecran de checkout complet ar deschide `url` într-un `WebView` sau în
/// browserul extern; vezi nota din `order_confirmation_screen.dart`.
class PaymentRedirect {
  final String url;
  final String method;
  final Map<String, dynamic>? fields;

  const PaymentRedirect({required this.url, required this.method, this.fields});

  factory PaymentRedirect.fromJson(Map<String, dynamic> json) {
    return PaymentRedirect(
      url: json['url'] as String,
      method: json['method'] as String,
      fields: json['fields'] as Map<String, dynamic>?,
    );
  }
}

/// Răspunsul complet al `POST /api/v1/checkout` (Partea 12): comanda creată
/// plus, opțional, redirecționarea de plată. Confirmat prin curl live —
/// `payment` stă lângă `data`, NU în interiorul ei:
///
/// ```json
/// { "data": { "number": "CMD-000002", ... }, "payment": null }
/// ```
class CheckoutResult {
  final OrderDetail order;
  final PaymentRedirect? payment;

  const CheckoutResult({required this.order, this.payment});
}
