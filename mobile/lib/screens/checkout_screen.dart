import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../api_service.dart';
import '../models/shipping_method.dart';
import '../state/cart_provider.dart';
import 'order_confirmation_screen.dart';

/// Un cod + o etichetă pentru o metodă de plată. Spre deosebire de livrare,
/// API-ul NU expune un `GET /checkout/payment-methods` (vezi `api-design.md`,
/// Partea 12) — lista de mai jos e cablată static în aplicație, pe aceleași
/// coduri pe care `PaymentManager` le acceptă pe server (`mock`, `netopia`,
/// `payu`; Părțile 8 și 11).
class _PaymentOption {
  const _PaymentOption(this.code, this.label);

  final String code;
  final String label;
}

const List<_PaymentOption> _paymentOptions = [
  _PaymentOption('mock', 'Ramburs la livrare'),
  _PaymentOption('netopia', 'Card bancar — Netopia'),
  _PaymentOption('payu', 'Card bancar — PayU'),
];

/// Ecranul de checkout: adresă (facturare +, opțional, livrare separată),
/// metodă de livrare (radio, cu cost live din `GET /checkout/shipping-methods`)
/// și metodă de plată, apoi `POST /checkout`. La succes, coșul local se
/// golește (serverul l-a golit deja prin `PlaceOrder`) și aplicația trece la
/// ecranul de confirmare cu numărul comenzii.
class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  // Instanța PARTAJATĂ (main.dart), nu una proprie — altfel un 401 aici n-ar
  // declanșa `onUnauthorized`/`forceLogout` (acela e cablat doar pe instanța
  // din Provider), vezi comentariul din `main.dart`.
  late final ApiService _api;

  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _county = TextEditingController();
  final _city = TextEditingController();
  final _street = TextEditingController();
  final _postalCode = TextEditingController();

  bool _differentShipping = false;
  final _shipName = TextEditingController();
  final _shipPhone = TextEditingController();
  final _shipCounty = TextEditingController();
  final _shipCity = TextEditingController();
  final _shipStreet = TextEditingController();
  final _shipPostalCode = TextEditingController();

  late final Future<List<ShippingMethod>> _shippingMethodsFuture;
  String? _shippingCode;
  String _paymentCode = _paymentOptions.first.code;
  bool _placing = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _api = context.read<ApiService>();
    _shippingMethodsFuture = _api.shippingMethods();
    // Preselectăm prima metodă de livrare de îndată ce sosește lista, fără să
    // mutăm starea în timpul `build()`-ului (FutureBuilder ar re-rula asta la
    // fiecare rebuild altfel).
    _shippingMethodsFuture.then((methods) {
      if (mounted && methods.isNotEmpty) {
        setState(() => _shippingCode = methods.first.code);
      }
    });
  }

  @override
  void dispose() {
    for (final controller in [
      _name,
      _phone,
      _county,
      _city,
      _street,
      _postalCode,
      _shipName,
      _shipPhone,
      _shipCounty,
      _shipCity,
      _shipStreet,
      _shipPostalCode,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  Map<String, String> _billingAddress() => {
        'name': _name.text.trim(),
        'phone': _phone.text.trim(),
        'county': _county.text.trim(),
        'city': _city.text.trim(),
        'street': _street.text.trim(),
        'postal_code': _postalCode.text.trim(),
      };

  Map<String, String> _shippingAddress() => _differentShipping
      ? {
          'name': _shipName.text.trim(),
          'phone': _shipPhone.text.trim(),
          'county': _shipCounty.text.trim(),
          'city': _shipCity.text.trim(),
          'street': _shipStreet.text.trim(),
          'postal_code': _shipPostalCode.text.trim(),
        }
      : _billingAddress();

  Future<void> _submit() async {
    final formValid = _formKey.currentState!.validate();

    if (!formValid || _shippingCode == null) {
      setState(() {
        if (_shippingCode == null) _error = 'Alege o metodă de livrare.';
      });
      return;
    }

    setState(() {
      _placing = true;
      _error = null;
    });

    try {
      final result = await _api.checkout(
        billing: _billingAddress(),
        shipping: _shippingAddress(),
        shippingCode: _shippingCode!,
        paymentCode: _paymentCode,
      );

      if (!mounted) return;

      // Serverul a golit deja coșul (PlaceOrder, Partea 8/9) — curățăm doar
      // starea locală, fără un nou apel de rețea.
      context.read<CartProvider>().clearLocal();

      await Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => OrderConfirmationScreen(result: result)),
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _placing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text('Adresă de facturare', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            _AddressFields(
              name: _name,
              phone: _phone,
              county: _county,
              city: _city,
              street: _street,
              postalCode: _postalCode,
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Adresă de livrare diferită'),
              value: _differentShipping,
              onChanged: (value) => setState(() => _differentShipping = value),
            ),
            if (_differentShipping) ...[
              const SizedBox(height: 8),
              Text('Adresă de livrare', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              _AddressFields(
                name: _shipName,
                phone: _shipPhone,
                county: _shipCounty,
                city: _shipCity,
                street: _shipStreet,
                postalCode: _shipPostalCode,
              ),
            ],
            const SizedBox(height: 16),
            Text('Livrare', style: Theme.of(context).textTheme.titleMedium),
            FutureBuilder<List<ShippingMethod>>(
              future: _shippingMethodsFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Padding(
                    padding: EdgeInsets.symmetric(vertical: 16),
                    child: Center(child: CircularProgressIndicator()),
                  );
                }

                if (snapshot.hasError) {
                  return Text('Nu am putut încărca metodele de livrare.\n${snapshot.error}');
                }

                final methods = snapshot.data ?? const <ShippingMethod>[];

                return Column(
                  children: [
                    for (final method in methods)
                      RadioListTile<String>(
                        contentPadding: EdgeInsets.zero,
                        value: method.code,
                        groupValue: _shippingCode,
                        onChanged: (value) => setState(() => _shippingCode = value),
                        title: Text(method.label),
                        subtitle: Text(method.cost.formatted),
                      ),
                  ],
                );
              },
            ),
            const SizedBox(height: 16),
            Text('Plată', style: Theme.of(context).textTheme.titleMedium),
            for (final option in _paymentOptions)
              RadioListTile<String>(
                contentPadding: EdgeInsets.zero,
                value: option.code,
                groupValue: _paymentCode,
                onChanged: (value) => setState(() => _paymentCode = value!),
                title: Text(option.label),
              ),
            if (_error != null) ...[
              const SizedBox(height: 8),
              Text(_error!, style: const TextStyle(color: Colors.redAccent)),
            ],
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _placing ? null : _submit,
              child: _placing
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Plasează comanda'),
            ),
          ],
        ),
      ),
    );
  }
}

/// Cele șase câmpuri de adresă, refolosite atât pentru facturare cât și
/// pentru livrare (când diferă). Cheile trimise la `POST /checkout` sunt
/// snake_case (`postal_code`) — vezi `CheckoutController::addressRules`,
/// confirmat prin curl (Partea 12); observă contrastul cu `postalCode`
/// (camelCase) din comanda întoarsă, documentat în `models/order.dart`.
class _AddressFields extends StatelessWidget {
  const _AddressFields({
    required this.name,
    required this.phone,
    required this.county,
    required this.city,
    required this.street,
    required this.postalCode,
  });

  final TextEditingController name;
  final TextEditingController phone;
  final TextEditingController county;
  final TextEditingController city;
  final TextEditingController street;
  final TextEditingController postalCode;

  String? _required(String? value) => (value == null || value.trim().isEmpty) ? 'Câmp obligatoriu.' : null;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        TextFormField(
          controller: name,
          decoration: const InputDecoration(labelText: 'Nume complet'),
          validator: _required,
        ),
        TextFormField(
          controller: phone,
          keyboardType: TextInputType.phone,
          decoration: const InputDecoration(labelText: 'Telefon'),
          validator: _required,
        ),
        Row(
          children: [
            Expanded(
              child: TextFormField(
                controller: county,
                decoration: const InputDecoration(labelText: 'Județ'),
                validator: _required,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: TextFormField(
                controller: city,
                decoration: const InputDecoration(labelText: 'Oraș'),
                validator: _required,
              ),
            ),
          ],
        ),
        TextFormField(
          controller: street,
          decoration: const InputDecoration(labelText: 'Stradă și număr'),
          validator: _required,
        ),
        TextFormField(
          controller: postalCode,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(labelText: 'Cod poștal'),
          validator: _required,
        ),
        const SizedBox(height: 12),
      ],
    );
  }
}
