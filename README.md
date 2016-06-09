Data enricher
=============

[![Build Status](https://travis-ci.org/legalthings/data-enricher.svg?branch=master)](https://travis-ci.org/legalthings/data-enricher)
[![Coverage Status](https://coveralls.io/repos/legalthings/data-enricher/badge.svg?branch=master&service=github&)](https://coveralls.io/github/legalthings/data-enricher?branch=master)

Enrich objects by processing special properties.

#### Special properties

* `<ref>` - Resolve a reference to another part of the document using a dot key path
* `<ifset>` - Checks if a reference is null. If so, replace the object by null.
* `<switch>` - Choose one of the child properties based on a property in the document
* `<src>` - Load an external resource (through HTTP)
* `<merge>` - Merge a set of objects
* `<tpl>` - Parse text as [Mustache](https://mustache.github.io/) template
* `<transform>` - Transform the input using a function. The following functions are available
  * [`hash:algo`](http://php.net/hash) - Replace `algo` with the algoritm
  * [`base64_encode`](http://php.net/base64_encode)
  * [`base64_decode`](http://php.net/base64_decode)
  * [`json_encode`](http://php.net/json_encode)
  * [`json_decode`](http://php.net/json_decode)
  * [`serialize`](http://php.net/serialize)
  * [`unserialize`](http://php.net/unserialize)
* `<jmespath>` - Project an object using the [JMESPath](http://jmespath.org/) query language

## Installation

    composer require legalthings/data-enricher

## Usage

#### Source

```json
{
  "foo": {
    "bar": {
      "qux": 12345,
    },
    "term": "data enrichment",
    "city": "Amsterdam",
    "country": "Netherlands"
  },
  "amount": {
    "_ref" : "foo.bar.qux"
  },
  "message": {
    "_tpl": "I want to go to {{ foo.city }}, {{ foo.country }}"
  },
  "shipping": {
    "_switch": "foo.city",
    "USA": "UPS",
    "Netherlands": "PostNL",
    "_other": "DHL"
  },
  "user" : {
    "_src": "https://api.example.com/users/9870"
  },
  "search_results": {
    "_src": {
      "_tpl": "http://api.duckduckgo.com/?q={{ foo.term }}&format=json"
    },
    "_jmespath": "RelatedTopics[].{url: FirstURL, description: Text}"
  },
  "profile": {
    "_merge": [
      { "_ref": "foo.bar" },
      { "_src": "https://api.example.com/zoo/99" },
      {
        "apples": 100,
        "pears": 220
      }
    ]
  }
}
```

#### PHP script

```php
$json = file_get_contents('source.json');
$object = json_decode($json);

DataEnricher::process($object);

echo json_encode($object, JSON_PRETTY_PRINT);
```

#### Result

```json
{
  "foo": {
    "bar": {
      "qux": 12345,
    },
    "term": "DuckDuckGo",
    "city": "Amsterdam",
    "country": "Netherlands"
  },
  "amount": 12345,
  "message": "I want to go to Amsterdam, Netherlands",
  "shipping": "PostNL",
  "user" : {
    "id": "9870",
    "name": "John Doe",
    "email": "john@example.com"
  },
  "search_results": [
    {
      "url": "https://duckduckgo.com/Names_Database",
      "description": "Names Database - The Names Database is a partially defunct social network, owned and operated by Classmates.com, a wholly owned subsidiary of United Online. The site does not appear to be significantly updated since 2008, and has many broken links and display issues."
    },
    {
      "url": "https://duckduckgo.com/c/Tor_hidden_services",
      "description": "Tor hidden services"
    },
    {
      "url": "https://duckduckgo.com/c/Internet_privacy_software",
      "description": "Internet privacy software"
    }
  ],
  "profile": {
    "qux": 12345,
    "zoop": "P99",
    "zooq": "Q99",
    "apples": 100,
    "pears": 220
  }
}
```
