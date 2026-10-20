
import { useState } from 'react';
import api from '../services/api';

function ApiTest() {
  const [message, setMessage] = useState('Not tested yet');
  const [loading, setLoading] = useState(false);

  async function testConnection() {
    setLoading(true);
    setMessage('Connecting to Laravel...');

    try {
      const response = await api.get('/courses');
      setMessage(
        `Connected successfully! Laravel returned HTTP ${response.status}.`
      );
    } catch (error) {
      if (error.response) {
        setMessage(
          `Laravel responded with HTTP ${error.response.status}. The server is reachable, but the request was not successful.`
        );
      } else {
        setMessage(
          'Connection failed. Check that Laravel is running and the API URL is correct.'
        );
      }
    } finally {
      setLoading(false);
    }
  }

  return (
    <div style={{ padding: '24px' }}>
      <h2>CampusConnect API Connection Test</h2>

      <button onClick={testConnection} disabled={loading}>
        {loading ? 'Testing...' : 'Test Laravel Connection'}
      </button>

      <p>{message}</p>
    </div>
  );
}

export default ApiTest;
