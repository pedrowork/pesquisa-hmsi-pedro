# Guia de Boas Práticas de Segurança para Desenvolvedores

## 1. Autenticação e Autorização

### ✅ Boas Práticas

```php
// ✅ SEMPRE verificar autenticação
if (!Auth::check()) {
    return redirect()->route('login');
}

// ✅ SEMPRE verificar permissões específicas
if (!$user->hasPermission('users.edit')) {
    abort(403);
}

// ✅ Usar middleware de autorização
Route::middleware('permission:users.edit')->group(function () {
    // rotas protegidas
});
```

### ❌ Evitar

```php
// ❌ NUNCA confiar apenas em verificação de frontend
// ❌ NUNCA usar permissões hardcoded
// ❌ NUNCA pular verificações de autorização
```

## 2. Validação de Entrada

### ✅ Boas Práticas

```php
// ✅ SEMPRE validar entrada do usuário
$validated = $request->validate([
    'email' => ['required', 'email', 'max:255'],
    'password' => ['required', Password::defaults()],
]);

// ✅ Usar regras de validação específicas
'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
'file' => ['required', File::image()->max(2048)],
```

### ❌ Evitar

```php
// ❌ NUNCA confiar em dados não validados
$email = $request->input('email'); // PERIGOSO!

// ❌ NUNCA usar eval() ou funções similares
eval($userInput); // EXTREMAMENTE PERIGOSO!
```

## 3. Proteção contra SQL Injection

### ✅ Boas Práticas

```php
// ✅ SEMPRE usar Query Builder ou Eloquent
User::where('email', $email)->first();
DB::table('users')->where('email', $email)->first();

// ✅ Usar prepared statements para queries complexas
DB::select('SELECT * FROM users WHERE email = ?', [$email]);
```

### ❌ Evitar

```php
// ❌ NUNCA concatenar strings em queries SQL
DB::select("SELECT * FROM users WHERE email = '{$email}'"); // PERIGOSO!
```

## 4. Proteção contra XSS (Cross-Site Scripting)

### ✅ Boas Práticas

```php
// ✅ Laravel escapa automaticamente em Blade
{{ $userInput }} // Seguro

// ✅ Para HTML não escapado, usar com cuidado
{!! $trustedHtml !!} // Apenas se confiar 100%

// ✅ Validar e sanitizar entrada
$clean = strip_tags($userInput);
```

### ❌ Evitar

```php
// ❌ NUNCA renderizar HTML não validado
{!! $userInput !!} // PERIGOSO se não validado!
```

## 5. Proteção CSRF

### ✅ Boas Práticas

```php
// ✅ Laravel protege automaticamente rotas POST/PUT/DELETE
// ✅ Sempre incluir @csrf em formulários Blade
<form method="POST">
    @csrf
    ...
</form>

// ✅ Para APIs, usar tokens CSRF ou autenticação adequada
```

## 6. Senhas e Criptografia

### ✅ Boas Práticas

```php
// ✅ SEMPRE usar Hash::make() para senhas
$user->password = Hash::make($password);

// ✅ NUNCA armazenar senhas em texto plano
// ✅ Usar bcrypt (padrão do Laravel)

// ✅ Verificar senhas com Hash::check()
if (Hash::check($plainPassword, $hashedPassword)) {
    // senha correta
}
```

### ❌ Evitar

```php
// ❌ NUNCA armazenar senhas em texto plano
$user->password = $password; // PERIGOSO!

// ❌ NUNCA usar MD5 ou SHA1 para senhas
md5($password); // INSECURO!
```

## 7. Upload de Arquivos

### ✅ Boas Práticas

```php
// ✅ SEMPRE validar tipo e tamanho
$request->validate([
    'file' => [
        'required',
        File::image()
            ->max(2048) // 2MB
            ->dimensions(Rule::dimensions()->maxWidth(2000)),
    ],
]);

// ✅ Armazenar fora do diretório web
$path = $request->file('photo')->store('photos', 'public');

// ✅ Validar extensão real do arquivo
$mimeType = $request->file('photo')->getMimeType();
```

### ❌ Evitar

```php
// ❌ NUNCA confiar apenas na extensão do arquivo
// ❌ NUNCA permitir upload sem validação
// ❌ NUNCA executar arquivos enviados pelo usuário
```

## 8. Logs e Auditoria

### ✅ Boas Práticas

```php
// ✅ Registrar ações críticas
app(\App\Services\AuditService::class)->log(
    'user_updated',
    'user_management',
    'Usuário atualizado',
    $user,
    $oldValues,
    $newValues
);

// ✅ Mascarar dados sensíveis em logs
$masked = DataMaskingHelper::maskSensitiveData($data);
```

### ❌ Evitar

```php
// ❌ NUNCA logar senhas ou tokens completos
Log::info('Password: ' . $password); // PERIGOSO!

// ❌ NUNCA logar dados sensíveis sem mascaramento
```

## 9. Sessões e Cookies

### ✅ Boas Práticas

```php
// ✅ Configurar cookies seguros em produção
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',

// ✅ Regenerar ID de sessão após login
$request->session()->regenerate();
```

## 10. Tratamento de Erros

### ✅ Boas Práticas

```php
// ✅ Não expor detalhes de erro em produção
try {
    // código
} catch (\Exception $e) {
    Log::error('Erro: ' . $e->getMessage());
    return response()->json(['error' => 'Erro interno'], 500);
}

// ✅ Usar mensagens genéricas para usuários
// ✅ Logar detalhes completos para desenvolvedores
```

### ❌ Evitar

```php
// ❌ NUNCA expor stack traces em produção
throw $e; // PERIGOSO em produção!

// ❌ NUNCA expor detalhes de banco de dados
```

## 11. Headers de Segurança

### ✅ Boas Práticas

```php
// ✅ Usar middleware SecurityHeaders
// ✅ Configurar CSP, HSTS, X-Frame-Options
// ✅ Remover headers que expõem informações do servidor
```

## 12. Dependências e Pacotes

### ✅ Boas Práticas

```bash
# ✅ Manter dependências atualizadas
composer update

# ✅ Verificar vulnerabilidades
composer audit

# ✅ Usar apenas pacotes confiáveis e mantidos
```

## 13. Variáveis de Ambiente

### ✅ Boas Práticas

```php
// ✅ SEMPRE usar variáveis de ambiente para dados sensíveis
$apiKey = env('API_KEY');

// ✅ NUNCA commitar .env
// ✅ Usar .env.example como template
```

### ❌ Evitar

```php
// ❌ NUNCA hardcodar credenciais
$password = 'senha123'; // PERIGOSO!

// ❌ NUNCA commitar arquivos .env
```

## 14. Queries e Performance

### ✅ Boas Práticas

```php
// ✅ Usar eager loading para evitar N+1
User::with('roles')->get();

// ✅ Usar índices em colunas frequentemente consultadas
// ✅ Validar e sanitizar antes de queries
```

## 15. Testes de Segurança

### ✅ Boas Práticas

```php
// ✅ Testar autenticação e autorização
public function test_unauthorized_user_cannot_access()
{
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/admin/users');
    $response->assertStatus(403);
}

// ✅ Testar validação de entrada
// ✅ Testar proteção CSRF
```

## Checklist de Segurança

Antes de fazer commit, verifique:

- [ ] Todas as entradas do usuário são validadas?
- [ ] Permissões são verificadas adequadamente?
- [ ] Dados sensíveis não são logados?
- [ ] Senhas são hasheadas?
- [ ] Uploads de arquivo são validados?
- [ ] Queries SQL são protegidas contra injection?
- [ ] Headers de segurança estão configurados?
- [ ] Tratamento de erros não expõe informações sensíveis?
- [ ] Dependências estão atualizadas?
- [ ] Variáveis de ambiente são usadas para dados sensíveis?

## Recursos Adicionais

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Documentation](https://laravel.com/docs/security)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)

---

**Lembre-se**: Segurança não é um recurso adicional, é uma responsabilidade fundamental!

