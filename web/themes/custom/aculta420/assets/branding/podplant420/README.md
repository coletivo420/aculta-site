# Podplant420 branding assets

Static Podplant420 branding approved for use by the ACULTA420 theme.

## Identity variants

| Variant | Intended surface |
| --- | --- |
| `stacked-on-dark` | Light mark on dark surfaces |
| `stacked-on-light` | Dark mark on light surfaces |
| `horizontal-on-dark` | Horizontal light mark on dark surfaces |
| `horizontal-on-light` | Horizontal dark mark on light surfaces |

Runtime WebP derivatives are deliberately small and canonical:

- stacked: 128, 256 and 512 px;
- horizontal: 240 and 480 px.

The complete delivery package also preserves larger raster derivatives for future consumers. They are not duplicated in the theme until a real consumer requires them.

## Usage

These assets are suitable for project cards, podcast showcases, editorial sections, listings, project pages, footers, promotional pieces and external-link integrations.

They are **not** a main-header identity in this task. Do not alter the Institution Bar or Domain Header, do not create a Podplant420 Domain purpose, and do not hard-code Podplant420 behavior into generic SDC components. Consumers must receive the image through props, slots or render arrays prepared by the appropriate layer.

No CSS filter may recolor the logo. Do not redraw or auto-vectorize raster originals.

## Editorial media

Episode art, guest photos, covers, thumbnails and other managed content do not belong in this directory. Use Drupal Media/File API, Image Styles and Responsive Image for those assets.

## Icons

No icon-only mark was derived because no independently approved icon source was supplied. Cropping a symbol out of the logo would modify the approved identity.

See `source/README.md` and `../../../docs/branding-podplant420.md` for provenance, accessibility and anti-regression rules.
