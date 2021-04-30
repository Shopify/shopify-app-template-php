import React from 'react';
import ReactDOM from 'react-dom';

function App() {
    return (
        <h1>Hello World</h1>
    );
}

export default App;

if (document.getElementById('app')) {
    ReactDOM.render(<App />, document.getElementById('app'));
}
