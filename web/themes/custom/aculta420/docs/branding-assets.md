# Branding assets

Assets de marca ficam em `assets/branding/aculta`.

## Estrutura

```text
assets/branding/aculta/
├── source/
│   ├── logo-aculta-horizontal-branco.png
│   ├── logo-aculta-horizontal-preto.png
│   ├── logo-aculta-quadrada-branca.png
│   └── logo-aculta-quadrada-preta.png
└── web/
    ├── aculta_favicon.ico
    ├── favicon-192.png
    ├── favicon-512.png
    ├── logo-aculta-horizontal-branco-900x300.png
    ├── logo-aculta-horizontal-preto-900x300.png
    ├── logo-aculta-quadrada-branca-512x512.png
    └── logo-aculta-quadrada-preta-512x512.png
```

## Convenção

`source/` contém os originais raster mantidos como referência de marca.

`web/` contém exports preparados para uso web e favicons.

O branding block usa atualmente:

`assets/branding/aculta/web/logo-aculta-horizontal-branco-900x300.png`

## Regras

- não substituir logo aprovado por Bootstrap/text branding genérico;
- não recolorir assets para introduzir novas cores de identidade;
- preservar proporções;
- evitar gerar múltiplos derivados sem uso concreto;
- registrar aqui qualquer novo asset oficial e onde ele é utilizado;
- não mover assets sem atualizar preprocess/templates e documentação no mesmo commit.

Os arquivos binários de marca não devem ser recomprimidos ou alterados incidentalmente durante refatoração de código.
