/// O categorie de catalog, așa cum o întoarce `GET /api/v1/categories`
/// (`CategoryResource`, Partea 12). Când `children` e prezent în JSON,
/// întregul arbore de categorii vine într-un singur răspuns.
class Category {
  final int id;
  final String name;
  final String slug;
  final int? parentId;
  final List<Category> children;

  const Category({
    required this.id,
    required this.name,
    required this.slug,
    this.parentId,
    this.children = const [],
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    final rawChildren = json['children'] as List<dynamic>? ?? const [];

    return Category(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      parentId: json['parent_id'] as int?,
      children: rawChildren
          .map((child) => Category.fromJson(child as Map<String, dynamic>))
          .toList(),
    );
  }
}
