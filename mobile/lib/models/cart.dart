import 'money.dart';

/// O linie din coș, așa cum o serializează `CartResource` (Partea 12).
///
/// GOTCHA confirmat prin curl live împotriva serverului (nu doar din citirea
/// codului): aici `variant_id` vine ca STRING (`"8"`), pentru că
/// `Modules\Core\DataObjects\CartLine::$variantId` e un `string` intern —
/// cartul ține id-urile de variantă ca și chei de array/sesiune.
/// `OrderItemLine` (din `order.dart`, tot din `OrderResource`) primește în
/// schimb un `int`, pentru că acolo e coloana `variant_id` a modelului
/// Eloquent `OrderItem`. Aceeași aplicație, două forme diferite pentru
/// „același" câmp — de-aia `fromJson` de mai jos trece prin
/// `int.parse(...toString())` în loc de un cast direct, ca să supraviețuiască
/// la ambele forme.
class CartLine {
  final int variantId;
  final String name;
  final int quantity;
  final Money unitPrice;
  final Money lineTotal;

  const CartLine({
    required this.variantId,
    required this.name,
    required this.quantity,
    required this.unitPrice,
    required this.lineTotal,
  });

  factory CartLine.fromJson(Map<String, dynamic> json) {
    return CartLine(
      variantId: int.parse(json['variant_id'].toString()),
      name: json['name'] as String,
      quantity: json['quantity'] as int,
      unitPrice: Money.fromJson(json['unit_price'] as Map<String, dynamic>),
      lineTotal: Money.fromJson(json['line_total'] as Map<String, dynamic>),
    );
  }
}

/// Coșul complet — forma întoarsă de `GET/POST/PATCH/DELETE /api/v1/cart`,
/// mereu învelită în `{ "data": {...} }` (un singur `CartResource`, nu o
/// colecție paginată). `CartProvider` ține o singură instanță din acest tip,
/// înlocuită integral după fiecare mutație (adăugare/actualizare/ștergere) —
/// nu există nicio actualizare optimistă/parțială pe client.
class CartData {
  final int itemCount;
  final Money subtotal;
  final List<CartLine> lines;

  const CartData({
    required this.itemCount,
    required this.subtotal,
    required this.lines,
  });

  bool get isEmpty => lines.isEmpty;

  factory CartData.fromJson(Map<String, dynamic> json) {
    final rawLines = json['lines'] as List<dynamic>? ?? const [];

    return CartData(
      itemCount: json['item_count'] as int,
      subtotal: Money.fromJson(json['subtotal'] as Map<String, dynamic>),
      lines: rawLines.map((line) => CartLine.fromJson(line as Map<String, dynamic>)).toList(),
    );
  }
}
