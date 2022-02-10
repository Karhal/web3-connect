[![Depfu](https://badges.depfu.com/badges/39be77dd94720ddf864ac11ef638d293/overview.svg)](https://badges.depfu.com/github/Karhal/web3-connect?project_id=34082)
[![codecov](https://codecov.io/gh/Karhal/web3-connect/branch/main/graph/badge.svg?token=4UL8g1hRfe)](https://codecov.io/gh/Karhal/web3-connect)

# Wallet Connect Bundle

## Description

This Symfony bundle let your users authenticate with their ethereum wallet.
To do this you only need them to sign a message with their wallet.

This bundle uses the eip-4361, it is meant to work with the [spruceid/siwe](https://github.com/spruceid/siwe) library
### Why ?

Your wallet lets you connect to any decentralized application using your Ethereum account. It's like a login you can use across many dapps.
This bundle is here to bring this feature to every Symfony website.

## Getting started

### Installation

```bash
composer require karhal/web3-connect
```

```php
<?php

//config/bundles.php

return [
    //... ,
    Karhal\Web3ConnectBundle\Web3ConnectBundle::class => ['all' => true],
];
```

### Configuration

config/packages/web3_connect.yaml

```yaml
wallet_connect:
  user_class: App\Entity\User
  jwt_secret: MySecretPhrase
```
config/packages/security.yaml

```yaml
security:
    #...
    providers:
        #...
        web3_user_provider:
            entity:
                class: App\Entity\User
                property: walletAddress
    firewalls:
        #...
        web3:
            custom_authenticators:
                - Karhal\Web3ConnectBundle\Security\Web3Authenticator
            provider: web3_user_provider

        main: #...
```

config/routes.yaml

```yaml
web3_link:
  resource: "@Web3ConnectBundle/config/routes.yaml"
```
Update the model of the class representing the user by implementing the Web3UserInterface
```php

//...
use use Karhal\Web3ConnectBundle\Model\Web3UserInterface;
//...

class User implements Web3UserInterface
{
    //...

    public function getWalletAddress(): string
    {
        return $this->walletAddress;
    }

    public function setWalletAddress(string $wallet)
    {
        $this->walletAddress = $wallet;
    }
}
```

Then update your storage

```bash
php bin/console doctrine:mig:diff
php bin/console doctrine:mig:mig
````

Now you're good to go

# Usage

The bundle provides a signature route to generate the message to sign.
Once the message signed, send it back with the address which signed it.

## Step 1: Get the nonce

Before each signature, get the nonce from the backend

```javascript
    const res = await fetch(`${BACKEND_ADDR}/web3_nonce`, {
    credentials: 'include',
    mode: 'cors',
    headers: {
        'Accept': 'application/json',
    },
});
```

## Step 2: Generate the message 

```javascript
    const message = await createSiweMessage(
        await signer.getAddress(),
        'Sign in with Ethereum to the app.'
    );
```

## Step 3: Send the message with his signature

```javascript
    const res = await fetch(`${BACKEND_ADDR}/web3_verify`, {
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({ message, signature }),
    credentials: 'include',
    method: "POST",
    mode: 'cors',
});
```

Full example with the [spruceid/siwe-quickstart example](https://github.com/spruceid/siwe-quickstart/tree/main/03_complete_app/frontend) 

```javascript
import { ethers } from 'ethers';
import { SiweMessage } from 'siwe';

const domain = window.location.host;
const origin = window.location.origin;
const provider = new ethers.providers.Web3Provider(window.ethereum);
const signer = provider.getSigner();

const BACKEND_ADDR = "http://127.0.0.1:8000";
async function createSiweMessage(address, statement) {
    const res = await fetch(`${BACKEND_ADDR}/web3_nonce`, {
        credentials: 'include',
        mode: 'cors',
        headers: {
            'Accept': 'application/json',
        },
    });
    const message = new SiweMessage({
        domain,
        address,
        statement,
        uri: origin,
        version: '1',
        chainId: '1',
        nonce: (await res.json()).nonce
    });
    return message.prepareMessage();
}

function connectWallet() {
    provider.send('eth_requestAccounts', [])
        .catch(() => console.log('user rejected request'));
}

async function signInWithEthereum() {
    const message = await createSiweMessage(
        await signer.getAddress(),
        'Sign in with Ethereum to the app.'
    );
    const signature = await signer.signMessage(message);

    const res = await fetch(`${BACKEND_ADDR}/web3_verify`, {
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ message, signature }),
        credentials: 'include',
        method: "POST",
        mode: 'cors',
    });
    document.getElementById('infoUser').innerText = 'Welcome '+  (await res.json()).identifier;
}


const connectWalletBtn = document.getElementById('connectWalletBtn');
const siweBtn = document.getElementById('siweBtn');
connectWalletBtn.onclick = connectWallet;
siweBtn.onclick = signInWithEthereum;
```

The bundle will verify the signed message is owned by the address. If true, the owner of the address from your storage will be loaded as a JWT token.

Response:

````json
{
    "identifier": "foo@bar.com",
    "token": "eyJ0eXs[...]",
    "data": {}
}
````

## Step 4: Access authorized routes

You can now make requests to authorized routes by adding the `http_header` to the headers of your requests with the value of the just generated token.

```javascript
    const res = await fetch(`${BACKEND_ADDR}/private_url`, {
    headers: {
        'Accept': 'application/json',
        'X-AUTH-WEB3TOKEN': 'eyJ0eXs[...]'
    },
});
```

## Step 5: Customize the bundle Response

Just before returning the Response the bundle dispatch a `DataInitializedEvent` event providing a data array you can fill to provide some extra information to your front.

You can add any data you want by listening to this event and call his `setData` method.

```php
<?php

namespace App\EventListener;

use Karhal\Web3ConnectBundle\Event\DataInitializedEvent;

class Web3LoginEventListener
{
    public function onWeb3userDataInitialized(DataInitializedEvent $event)
    {
        $event->setData(['foo' => 'bar']);
    }
}
```

Response:

```json
{
    "identifier": "foo@bar.com",
    "token": "eyJ0eXs[...]",
    "data": {
        "foo": "bar"
    }
}
```

# Resources

- [Fast Elliptic Curve Cryptography in PHP](https://github.com/simplito/elliptic-php)
- [Ethereum-PHP](https://github.com/digitaldonkey/ethereum-php)
- https://medium.com/fabric-ventures/what-is-web-3-0-why-it-matters-934eb07f3d2b
- https://medium.com/mycrypto/the-magic-of-digital-signatures-on-ethereum-98fe184dc9c7
- https://eips.ethereum.org/EIPS/eip-4361#message-field-descriptions

### What's an Ethereum wallet?

Ethereum wallets are applications that let you interact with your Ethereum account. Think of it like an internet banking app – without the bank. Your wallet lets you read your balance, send transactions and connect to applications.

<em>“No more remembering unique passwords for separate sites. No more creating unique email addresses for different services. No more having to worry about the site you are interacting with having your data stolen from them. Pure, self-sovereign control of your accounts across the Internet. No usernames, passwords, or identifying information other than the public key that is derived upon sign up.”</em>

# Tests

```bash
vendor/bin/phpunit 
```

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.