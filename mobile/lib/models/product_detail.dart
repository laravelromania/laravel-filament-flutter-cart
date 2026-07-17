import 'money.dart';

/// Referință scurtă la o categorie, așa cum apare în lista `categories` a
/// unui produs detaliat (nu are nevoie de `children` — arborele complet vine
/// doar din `GET /api/v1/categories`).
class ProductCategoryRef {
  final int id;
  final String name;
  final String slug;

  const ProductCategoryRef({
    required this.id,
    required this.name,
    required this.slug,
  });

  factory ProductCategoryRef.fromJson(Map<String, dynamic> json) {
    return ProductCategoryRef(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
    );
  }
}

/// O pereche atribut/valoare care identifică o variantă, ex. Culoare: Roșu.
/// `attribute` poate fi `null` dacă relația nu a fost încărcată pe server.
class VariantAttribute {
  final String? attribute;
  final String value;
  final String slug;

  const VariantAttribute({
    this.attribute,
    required this.value,
    required this.slug,
  });

  factory VariantAttribute.fromJson(Map<String, dynamic> json) {
    return VariantAttribute(
      attribute: json['attribute'] as String?,
      value: json['value'] as String,
      slug: json['slug'] as String,
    );
  }

  /// „Culoare: Roșu" — sau doar valoarea, dacă numele atributului lipsește.
  String get label => attribute != null ? '$attribute: $value' : value;
}

/// O variantă cumpărabilă a unui produs — SKU propriu, preț efectiv (poate
/// suprascrie prețul produsului), stoc și combinația de atribute care o
/// descrie. Selectorul de variantă din ecranul de detaliu se construiește din
/// lista `attributes` a fiecărei variante.
class ProductVariant {
  final int id;
  final String sku;
  final Money price;
  final int stock;
  final bool inStock;
  final List<VariantAttribute> attributes;

  const ProductVariant({
    required this.id,
    required this.sku,
    required this.price,
    required this.stock,
    required this.inStock,
    required this.attributes,
  });

  factory ProductVariant.fromJson(Map<String, dynamic> json) {
    final rawAttributes = json['attributes'] as List<dynamic>? ?? const [];

    return ProductVariant(
      id: json['id'] as int,
      sku: json['sku'] as String,
      price: Money.fromJson(json['price'] as Map<String, dynamic>),
      stock: json['stock'] as int,
      inStock: json['in_stock'] as bool,
      attributes: rawAttributes
          .map((attr) => VariantAttribute.fromJson(attr as Map<String, dynamic>))
          .toList(),
    );
  }

  /// Eticheta completă afișată pe chip-ul de selecție, ex. „Culoare: Roșu, Mărime: M".
  /// Cade pe SKU dacă varianta nu are nicio pereche atribut/valoare.
  String get label => attributes.map((attribute) => attribute.label).join(', ');
}

/// Produsul complet pentru ecranul de detaliu — forma întoarsă de
/// `GET /api/v1/products/{slug}` (`ProductDetailResource`, Partea 12):
/// descriere, galerie de imagini, categorii și, esențial, lista de variante
/// cumpărabile.
class ProductDetail {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final String? brand;
  final Money price;
  final bool inStock;
  final List<String> images;
  final List<ProductCategoryRef> categories;
  final List<ProductVariant> variants;

  const ProductDetail({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    this.brand,
    required this.price,
    required this.inStock,
    required this.images,
    required this.categories,
    required this.variants,
  });

  factory ProductDetail.fromJson(Map<String, dynamic> json) {
    final rawImages = json['images'] as List<dynamic>? ?? const [];
    final rawCategories = json['categories'] as List<dynamic>? ?? const [];
    final rawVariants = json['variants'] as List<dynamic>? ?? const [];

    return ProductDetail(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      description: json['description'] as String?,
      brand: json['brand'] as String?,
      price: Money.fromJson(json['price'] as Map<String, dynamic>),
      inStock: json['in_stock'] as bool,
      images: rawImages.cast<String>(),
      categories: rawCategories
          .map((category) => ProductCategoryRef.fromJson(category as Map<String, dynamic>))
          .toList(),
      variants: rawVariants
          .map((variant) => ProductVariant.fromJson(variant as Map<String, dynamic>))
          .toList(),
    );
  }
}
