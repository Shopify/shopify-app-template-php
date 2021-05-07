import React from 'react';
import ReactDOM from 'react-dom';
import createApp from "@shopify/app-bridge";
import {authenticatedFetch} from "@shopify/app-bridge-utils"
import {ApolloClient, gql, HttpLink, InMemoryCache} from '@apollo/client';

const TEST_QUERY = gql`query { shop {name} }`;

function App({host, apiKey}) {
    const app = createApp({
        apiKey: apiKey,
        host: host
    });

    const client = new ApolloClient({
        link: new HttpLink({
            credentials: 'same-origin',
            fetch: authenticatedFetch(app),
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
