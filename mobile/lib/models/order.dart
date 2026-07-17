import 'money.dart';

/// Statusul unei comenzi ca pereche valoare/etichetă — `value` e stabil și
/// bun pentru logică (ex. `status.value == 'pending'`), `label` e traducerea
/// în română, gata de afișat (`OrderStatus::label()` pe server, Partea 9).
class OrderStatus {
  final String value;
  final String label;

  const OrderStatus({required this.value, required this.label});

  factory OrderStatus.fromJson(Map<String, dynamic> json) {
    return OrderStatus(
      value: json['value'] as String,
      label: json['label'] as String,
    );
  }
}

/// O adresă așa cum apare înghețată pe comandă (`billing`/`shipping` din
/// `OrderResource`).
///
/// GOTCHA confirmat prin curl: aici cheia e `postalCode` (camelCase) — spre
/// deosebire de corpul cererii `POST /checkout`, unde `CheckoutController`
/// cere `postal_code` (snake_case). Backend-ul ACCEPTĂ o formă la intrare și
/// ÎNTOARCE alta la ieșire (`AddressData::$postalCode` e proprietatea PHP
/// camelCase, serializată ca atare pe comandă; validarea cererii, în schimb,
/// citește direct array-ul de input trimis de client). Cele două `fromJson`
/// de mai jos + trimiterea din `checkout_screen.dart` reflectă exact această
/// asimetrie, nu o presupunere.
class OrderAddress {
  final String name;
  final String phone;
  final String county;
  final String city;
  final String street;
  final String postalCode;

  const OrderAddress({
    required this.name,
    required this.phone,
    required this.county,
    required this.city,
    required this.street,
    required this.postalCode,
  });

  factory OrderAddress.fromJson(Map<String, dynamic> json) {
    return OrderAddress(
      name: json['name'] as String,
      phone: json['phone'] as String,
      county: json['county'] as String,
      city: json['city'] as String,
      street: json['street'] as String,
      postalCode: json['postalCode'] as String,
    );
  }
}

/// O linie de comandă — instantaneul produsului la momentul plasării (nume și
/// preț înghețate, nu o referință live la variantă/produs).
class OrderItemLine {
  final int variantId;
  final String name;
  final int quantity;
  final Money unitPrice;
  final Money lineTotal;

  const OrderItemLine({
    required this.variantId,
    required this.name,
    required this.quantity,
    required this.unitPrice,
    required this.lineTotal,
  });

  factory OrderItemLine.fromJson(Map<String, dynamic> json) {
    return OrderItemLine(
      // `variant_id` vine ca `int` aici (coloană Eloquent pe `order_items`),
      // spre deosebire de `CartLine.variantId`, care vine ca `String` — vezi
      // nota din cart.dart. `int.parse(...toString())` supraviețuiește la
      // ambele forme fără să presupună una anume.
      variantId: int.parse(json['variant_id'].toString()),
      name: json['name'] as String,
      quantity: json['quantity'] as int,
      unitPrice: Money.fromJson(json['unit_price'] as Map<String, dynamic>),
      lineTotal: Money.fromJson(json['line_total'] as Map<String, dynamic>),
    );
  }
}

/// O comandă așa cum apare într-un rând din `GET /api/v1/orders` — doar
/// câmpurile de care are nevoie o listă (număr, status, total, livrare,
/// dată). Ecranul de istoric (`orders_screen.dart`) folosește acest model mai
/// subțire; detaliul complet vine din [OrderDetail]. Backend-ul întoarce de
/// fapt același `OrderResource` complet la ambele endpoint-uri — [OrderSummary]
/// pur și simplu ignoră câmpurile pe care lista nu le afișează.
class OrderSummary {
  final String number;
  final OrderStatus status;
  final Money total;
  final String shippingLabel;
  final DateTime? createdAt;

  const OrderSummary({
    required this.number,
    required this.status,
    required this.total,
    required this.shippingLabel,
    this.createdAt,
  });

  factory OrderSummary.fromJson(Map<String, dynamic> json) {
    final rawCreatedAt = json['created_at'] as String?;

    return OrderSummary(
      number: json['number'] as String,
      status: OrderStatus.fromJson(json['status'] as Map<String, dynamic>),
      total: Money.fromJson(json['total'] as Map<String, dynamic>),
      shippingLabel: json['shipping_label'] as String,
      createdAt: rawCreatedAt != null ? DateTime.tryParse(rawCreatedAt) : null,
    );
  }
}

/// Comanda completă — forma întoarsă de `GET /api/v1/orders/{number}` și de
/// `POST /api/v1/checkout` (același `OrderResource`, Partea 12): adrese
/// înghețate, linii, toate totalurile în forma Money comună și AWB-ul
/// curierului, dacă a fost deja generat (Partea 10).
class OrderDetail {
  final String number;
  final String reference;
  final OrderStatus status;
  final String email;
  final String customerName;
  final String phone;
  final OrderAddress billing;
  final OrderAddress shipping;
  final Money itemsSubtotal;
  final String shippingCode;
  final String shippingLabel;
  final Money shippingTotal;
  final String paymentCode;
  final Money total;
  final String? awb;
  final DateTime? paidAt;
  final DateTime? createdAt;
  final List<OrderItemLine> items;

  const OrderDetail({
    required this.number,
    required this.reference,
    required this.status,
    required this.email,
    required this.customerName,
    required this.phone,
    required this.billing,
    required this.shipping,
    required this.itemsSubtotal,
    required this.shippingCode,
    required this.shippingLabel,
    required this.shippingTotal,
    required this.paymentCode,
    required this.total,
    this.awb,
    this.paidAt,
    this.createdAt,
    required this.items,
  });

  factory OrderDetail.fromJson(Map<String, dynamic> json) {
    final rawItems = json['items'] as List<dynamic>? ?? const [];
    final rawPaidAt = json['paid_at'] as String?;
    final rawCreatedAt = json['created_at'] as String?;

    return OrderDetail(
      number: json['number'] as String,
      reference: json['reference'] as String,
      status: OrderStatus.fromJson(json['status'] as Map<String, dynamic>),
      email: json['email'] as String,
      customerName: json['customer_name'] as String,
      phone: json['phone'] as String,
      billing: OrderAddress.fromJson(json['billing'] as Map<String, dynamic>),
      shipping: OrderAddress.fromJson(json['shipping'] as Map<String, dynamic>),
      itemsSubtotal: Money.fromJson(json['items_subtotal'] as Map<String, dynamic>),
      shippingCode: json['shipping_code'] as String,
      shippingLabel: json['shipping_label'] as String,
      shippingTotal: Money.fromJson(json['shipping_total'] as Map<String, dynamic>),
      paymentCode: json['payment_code'] as String,
      total: Money.fromJson(json['total'] as Map<String, dynamic>),
      awb: json['awb'] as String?,
      paidAt: rawPaidAt != null ? DateTime.tryParse(rawPaidAt) : null,
      createdAt: rawCreatedAt != null ? DateTime.tryParse(rawCreatedAt) : null,
      items: rawItems.map((item) => OrderItemLine.fromJson(item as Map<String, dynamic>)).toList(),
    );
  }
}
