/// O sumă de bani așa cum o serializează API-ul Laravel (`MoneyResource`,
/// Partea 12) — mereu aceeași formă, peste tot unde apare un preț:
///
/// ```json
/// { "minor": 12990, "formatted": "129,90 lei", "currency": "RON" }
/// ```
///
/// `minor` e valoarea întreagă în bani (niciodată un `double` — banii nu se
/// reprezintă ca număr în virgulă mobilă), `formatted` e string-ul gata de
/// afișat în română, `currency` e codul valutei. Aplicația NU face calcule
/// monetare pe client — formatarea vine deja făcută de server; ecranele
/// afișează direct `formatted` (sau `toString()`, care e identic).
class Money {
  final int minor;
  final String formatted;
  final String currency;

  const Money({
    required this.minor,
    required this.formatted,
    required this.currency,
  });

  factory Money.fromJson(Map<String, dynamic> json) {
    return Money(
      minor: json['minor'] as int,
      formatted: json['formatted'] as String,
      currency: json['currency'] as String,
    );
  }

  @override
  String toString() => formatted;
}
