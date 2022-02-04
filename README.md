[![Depfu](https://badges.depfu.com/badges/b9ad43be216e83c78365733dab73b8da/overview.svg)](https://depfu.com/repos/gitlab/Karhal/wallet-connect?project_id=33881)
[![codecov](https://codecov.io/gh/Karhal/web3-connect/branch/main/graph/badge.svg?token=4UL8g1hRfe)](https://codecov.io/gh/Karhal/web3-connect)

# Wallet Connect Bundle

## Description

This Symfony bundle let your users authenticate with their ethereum wallet.
To do this you only need them to sign a message with an address linked with their profile.

### Why ?

Your wallet lets you connect to any decentralized application using your Ethereum account. It's like a login you can use across many dapps.
This bundle is here to bring this feature to every Symfony website.

## Getting started

### Installation

```bash
composer require karhal/web3-connect-bundle
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
  sign_message: "Hey ! To log in just sign this with your wallet"
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

## Step 1: Get message to sign

For each user log in demand, the bundle generates a nonce from your configured signature message to make it unique to the user in session

```javascript
const message = await axios.get("/web3_nonce").then((res) => res.data);
```

## Step 2: User signs the Nonce 

The user now returns the signed message to the login route with his wallet address.

There are multiple ways to connect your front to the user's wallet. For this example, we'll be using `web3modal` and `ethers` libraries. 

```javascript
    axios.post("/web3_verify", {
    address: await web3.getSigner().getAddress(), 
    signature: await web3.getSigner().signMessage(message), 
});
```

Full example

```javascript
import axios from "axios";
import { ethers } from "ethers";
import Web3Modal from "web3modal";

const web3Modal = new Web3Modal({
    cacheProvider: true,
    providerOptions: {},
});

const onClick = async () => {
    const message = await axios.get("/web3_nonce").then((res) => res.data);
    const provider = await web3Modal.connect();

    provider.on("accountsChanged", () => web3Modal.clearCachedProvider());

    const web3 = new ethers.providers.Web3Provider(provider);

    axios.post("/web3_verify", {
        address: await web3.getSigner().getAddress(),
        signature: await web3.getSigner().signMessage(message),
    });
};
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

## Step 3: Access authorized routes

You can now make requests to authorized routes by adding the `http_header` to the headers of your requests with the value of the just generated token.

```javascript
axios.get('https://example.com/getSomethingPrivate', {
    headers: {
        'X-AUTH-WEB3TOKEN': 'eyJ0eXs[...]'
    }
})
```

## Step 4: Customize the bundle Response

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

### What's an Ethereum wallet?

Ethereum wallets are applications that let you interact with your Ethereum account. Think of it like an internet banking app – without the bank. Your wallet lets you read your balance, send transactions and connect to applications.

<em>“No more remembering unique passwords for separate sites. No more creating unique email addresses for different services. No more having to worry about the site you are interacting with having your data stolen from them. Pure, self-sovereign control of your accounts across the Internet. No usernames, passwords, or identifying information other than the public key that is derived upon sign up.”</em>

# Tests

```bash
vendor/bin/phpunit 
```

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.