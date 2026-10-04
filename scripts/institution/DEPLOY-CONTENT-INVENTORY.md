# Content entities required after full configuration import

`config/sync` contains the complete desired configuration, but Drupal content
entities are provisioned separately.

| Entity | Local state | Deployment handling |
| --- | --- | --- |
| Commerce Store | One default `online` store, entity ID `1`, UUID `32f75ad2-5d45-48bb-898e-d657d47d04f8`, BRL | Run `scripts/institution/provision-commerce-store.php` after full config import and after the canonical institutional block content is provisioned. The script aborts if those reviewed institutional fields differ or another Store exists. |
| Commerce Order | None | No donation setup creates an Order. Orders arise only when a person starts the Donation Flow. |
| Commerce Payment | None | No payment or payment fixture exists. |
| Commerce Payment Gateway | One disabled config entity `mercado_pago` | Imported by full config sync. It is not a content entity and contains empty credential fields. |
| Commerce products | None | Donation Flow uses its Donation Order Item; no product catalog was created. |
| Donation Order Item | Configured bundle `donation`; no content/order-item entities | Imported as configuration; runtime checkout creates order items. |

The Store has the official institution name, public email, and address already
used by the site. Its UUID is stable so the imported gateway's Store condition
continues to match the provisioned entity. Never create a second Store to
recover from a provisioning error; inspect the existing entity first.
