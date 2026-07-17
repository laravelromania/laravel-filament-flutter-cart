import 'money.dart';

/// Un produs așa cum apare într-un card de listare — forma întoarsă de
/// `GET /api/v1/products` (`ProductResource`, Partea 12): suficient pentru un
/// grid, fără să mai fie nevoie de un al doilea request. Detaliul complet
/// (variante, atribute, galerie) vine din `ProductDetail`.
class Product {
  final int id;
  final String name;
  final String slug;
  final String? brand;
  final Money price;
  final bool inStock;
  final String? image;

  const Product({
    required this.id,
    required this.name,
    required this.slug,
    this.brand,
    required this.price,
    required this.inStock,
    this.image,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      brand: json['brand'] as String?,
      price: Money.fromJson(json['price'] as Map<String, dynamic>),
      inStock: json['in_stock'] as bool,
      image: json['image'] as String?,
    );
  }
}
