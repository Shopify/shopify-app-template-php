import React from 'react';
import {withRouter} from "react-router-dom"
import {ClientRouter as AppBridgeClientRouter} from '@shopify/app-bridge-react';

function ClientRouter(props) {
    const {history} = props;
    return <AppBridgeClientRouter history={history} />;
}

export default withRouter(ClientRouter);
