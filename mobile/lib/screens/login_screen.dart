import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../state/auth_provider.dart';

/// Ecran unic de autentificare: login ȘI creare cont, comutate prin
/// [_isRegister] în loc de două ecrane separate.
///
/// Varianta veche (`LoginScreen`/`RegisterScreen` distincte) își comutau
/// locul cu `Navigator.pushReplacement` — ceea ce închidea imediat ecranul
/// înlocuit și rezolva Future-ul lui `push<bool>()` cu `null`, chiar și după
/// o ÎNREGISTRARE reușită. Un apelant ca `product_detail_screen.dart`
/// (`_addToCart`, care așteaptă `await Navigator.push<bool>(LoginScreen())`)
/// nu afla niciodată de succesul înregistrării, iar produsul nu se mai
/// adăuga în coș. Cu un singur ecran care doar își schimbă modul intern
/// (fără nicio navigare nouă), `Navigator.pop(context, true)` la succes e
/// mereu rezultatul pe care îl vede apelantul — indiferent dacă utilizatorul
/// s-a logat sau și-a creat cont chiar atunci.
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _passwordConfirmController = TextEditingController();
  bool _submitting = false;
  bool _isRegister = false;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _passwordConfirmController.dispose();
    super.dispose();
  }

  void _toggleMode() {
    setState(() {
      _isRegister = !_isRegister;
      // Un mesaj de eroare/validare de la modul anterior n-ar mai avea sens.
      _formKey.currentState?.reset();
    });
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _submitting = true);
    final auth = context.read<AuthProvider>();
    final ok = _isRegister
        ? await auth.register(
            name: _nameController.text.trim(),
            email: _emailController.text.trim(),
            password: _passwordController.text,
            passwordConfirmation: _passwordConfirmController.text,
          )
        : await auth.login(
            email: _emailController.text.trim(),
            password: _passwordController.text,
          );
    if (!mounted) return;
    setState(() => _submitting = false);

    if (ok) {
      // Rezultatul REAL al autentificării ajunge la orice apelant care a
      // deschis ecranul cu `push<bool>(...)` — login SAU register, la fel.
      Navigator.of(context).pop(true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            auth.error ?? (_isRegister ? 'Înregistrare eșuată.' : 'Autentificare eșuată.'),
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_isRegister ? 'Cont nou' : 'Autentificare')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              if (_isRegister) ...[
                TextFormField(
                  controller: _nameController,
                  decoration: const InputDecoration(labelText: 'Nume'),
                  validator: (value) =>
                      (value == null || value.isEmpty) ? 'Introdu numele.' : null,
                ),
                const SizedBox(height: 12),
              ],
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(labelText: 'Email'),
                validator: (value) =>
                    (value == null || value.isEmpty) ? 'Introdu adresa de email.' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _passwordController,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: _isRegister ? 'Parolă (minim 8 caractere)' : 'Parolă',
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) return 'Introdu parola.';
                  if (_isRegister && value.length < 8) {
                    return 'Parola trebuie să aibă minim 8 caractere.';
                  }
                  return null;
                },
              ),
              if (_isRegister) ...[
                const SizedBox(height: 12),
                TextFormField(
                  controller: _passwordConfirmController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Confirmă parola'),
                  validator: (value) =>
                      (value != _passwordController.text) ? 'Parolele nu coincid.' : null,
                ),
              ],
              const SizedBox(height: 24),
              FilledButton(
                onPressed: _submitting ? null : _submit,
                child: _submitting
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : Text(_isRegister ? 'Creează cont' : 'Intră în cont'),
              ),
              TextButton(
                onPressed: _submitting ? null : _toggleMode,
                child: Text(
                  _isRegister ? 'Ai deja cont? Autentifică-te' : 'Nu ai cont? Creează unul',
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
