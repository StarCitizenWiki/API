# Generell
Indentation mit 4 Spaces statt Tabs   
Coding Standard nach [Symfony 2](http://symfony.com/doc/current/contributing/code/standards.html)

# Namensrichtlinien
Protected und Private Methoden/Attribute __nicht__ mit `_` prefixen  
Methoden und Attribute wie folgt ordnen:  
*Bei Attributen sind `const` immer zuerst zu nennen*
* Public
* Protected
* Private

Korrekte Sortierung:
 ```PHP
 public const FOO = 'BAR';
 protected $baz;
 private $bar;  
   
 public fooFunc() {}
 private bazFunc() {}
 ```

Inkorrekte Sortierung:
 ```PHP
 public const FOO = 'BAR';
 private $bar;  
 protected $baz;
  
 protected quxFunc() {}
 public fooFunc() {}
 private bazFunc() {}
 ```

Sprechende Methodennamen verwenden  
Siehe `\App\Http\Controllers\Tools\FundImageController`
  
Sprechende Variablennamen verwenden  
`dataToTransform` statt `data`  

Laufvariablen immer als `i, j, k` etc. bezeichnen

# Tests
Um eine einwandfreie Funktionalität gewährleisten zu können soll jede erstellte Klasse im `\App` Namespace mindestens auf ihre `public` Methoden getestet werden.  
Bestehende Tests dürfen nicht durch neue Funktionalitäten gebrochen werden.  
Wird eine bereits getestete Klasse/Methode angepasst oder fundamental verändert, so sind die entsprechenden abdeckenden Tests ebenfalls abzuändern.