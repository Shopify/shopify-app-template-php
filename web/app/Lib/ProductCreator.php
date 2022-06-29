<?php

declare(strict_types=1);

namespace App\Lib;

use App\Exceptions\ShopifyProductCreatorException;
use Shopify\Auth\Session;
use Shopify\Clients\Graphql;

class ProductCreator
{
    private const CREATE_PRODUCTS_MUTATION = <<<'QUERY'
    mutation populateProduct($input: ProductInput!) {
        productCreate(input: $input) {
            product {
                id
            }
        }
    }
    QUERY;

    public static function call(Session $session, int $count)
    {
        $client = new Graphql($session->getShop(), $session->getAccessToken());

        for ($i = 0; $i < $count; $i++) {
            $response = $client->query(
                [
                    "query" => self::CREATE_PRODUCTS_MUTATION,
                    "variables" => [
                        "input" => [
                            "title" => self::randomTitle(),
                            "variants" => [["price" => self::randomPrice()]],
                        ]
                    ]
                ],
            );

            if ($response->getStatusCode() !== 200) {
                throw new ShopifyProductCreatorException($response->getBody()->__toString(), $response);
            }
        }
    }

    private static function randomTitle()
    {
        $adjective = self::ADJECTIVES[mt_rand(0, count(self::ADJECTIVES) - 1)];
        $noun = self::NOUNS[mt_rand(0, count(self::NOUNS) - 1)];

        return "$adjective $noun";
    }

    private static function randomPrice()
    {

        return (100.0 + mt_rand(0, 1000)) / 100;
    }

    private const ADJECTIVES = [
        "autumn",
        "hidden",
        "bitter",
        "misty",
        "silent",
        "empty",
        "dry",
        "dark",
        "summer",
        "icy",
        "delicate",
        "quiet",
        "white",
        "cool",
        "spring",
        "winter",
        "patient",
        "twilight",
        "dawn",
        "crimson",
        "wispy",
        "weathered",
        "blue",
        "billowing",
        "broken",
        "cold",
        "damp",
        "falling",
        "frosty",
        "green",
        "long",
    ];

    private const NOUNS = [
        "waterfall",
        "river",
        "breeze",
        "moon",
        "rain",
        "wind",
        "sea",
        "morning",
        "snow",
        "lake",
        "sunset",
        "pine",
        "shadow",
        "leaf",
        "dawn",
        "glitter",
        "forest",
        "hill",
        "cloud",
        "meadow",
        "sun",
        "glade",
        "bird",
        "brook",
        "butterfly",
        "bush",
        "dew",
        "dust",
        "field",
        "fire",
        "flower",
    ];
}
