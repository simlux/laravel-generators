# Laravel Generators
## Package Discovery
For package discovery add Service Provider to *extra* section in *composer.json*
```json
"extra": {
    "laravel": {
        "providers": [
            "Simlux\\LaravelGenerators\\Providers\\LaravelGeneratorsServiceProvider"
        ]
    }
},
```