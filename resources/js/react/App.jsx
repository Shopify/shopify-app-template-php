import React from 'react';
import ReactDOM from 'react-dom';
import createApp from "@shopify/app-bridge";
import {Redirect} from "@shopify/app-bridge/actions";
import {authenticatedFetch} from "@shopify/app-bridge-utils"
import {ApolloClient, gql, HttpLink, InMemoryCache} from '@apollo/client';

const TEST_QUERY = gql`query { shop {name} }`;

function userLoggedInFetch(app) {
    const fetchFunction = authenticatedFetch(app);

    return async (uri, options) => {
        const response = await fetchFunction(uri, options);

        if (response.headers.get("X-Shopify-API-Request-Failure-Reauthorize") === "1") {
            const authUrlHeader = response.headers.get("X-Shopify-API-Request-Failure-Reauthorize-Url");

            const redirect = Redirect.create(app);
            redirect.dispatch(Redirect.Action.APP, authUrlHeader);
            return null;
        }

        return response;
    };
}

function App({shop, host, apiKey}) {
    const app = createApp({
        shop: shop,
        host: host,
        apiKey: apiKey,
    });

    const client = new ApolloClient({
        link: new HttpLink({
            credentials: 'same-origin',
            fetch: userLoggedInFetch(app),
            uri: '/graphql'
        }),
        cache: new InMemoryCache()
    });

    client
        .query({
            query: TEST_QUERY
        })
        .then(result => console.log(result));

    return (
        <h1>Hello React</h1>
    );
}

export default App;

let appElement = document.getElementById('app');
if (appElement) {
    ReactDOM.render(<App {...(appElement.dataset)}/>, appElement);
}
