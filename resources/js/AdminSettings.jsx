import React, { useState, useEffect } from 'react';
import axios from 'axios';
import './AdminSettings.css';

const AdminSettings = () => {
    const [settings, setSettings] = useState({
        days_before_expiry: 7,
        review_url: '',
        api_key: '',
        enabled: true,
    });
    const [stats, setStats] = useState({
        total_dismissals: 0,
        unique_users: 0,
    });
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState({ type: '', text: '' });

    useEffect(() => {
        loadSettings();
        loadStats();
    }, []);

    const loadSettings = async () => {
        try {
            const response = await axios.get('/api/admin/trustpilot/settings');
            setSettings(response.data);
        } catch (error) {
            console.error('Error loading settings:', error);
            setMessage({ type: 'error', text: 'Failed to load settings' });
        } finally {
            setLoading(false);
        }
    };

    const loadStats = async () => {
        try {
            const response = await axios.get('/api/admin/trustpilot/stats');
            setStats(response.data);
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    };

    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        setSettings({
            ...settings,
            [name]: type === 'checkbox' ? checked : value,
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setMessage({ type: '', text: '' });

        try {
            const response = await axios.post('/api/admin/trustpilot/settings', settings);
            setMessage({ type: 'success', text: 'Settings saved successfully!' });
            setSettings(response.data.settings);
        } catch (error) {
            console.error('Error saving settings:', error);
            const errorText = error.response?.data?.errors 
                ? Object.values(error.response.data.errors).flat().join(', ')
                : 'Failed to save settings';
            setMessage({ type: 'error', text: errorText });
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return <div className="trustpilot-admin-loading">Loading...</div>;
    }

    return (
        <div className="trustpilot-admin-container">
            <div className="trustpilot-admin-header">
                <h1>Trustpilot Review Plugin Settings</h1>
                <p>Configure when and how to prompt users for Trustpilot reviews</p>
            </div>

            {message.text && (
                <div className={`trustpilot-admin-message trustpilot-admin-message-${message.type}`}>
                    {message.text}
                </div>
            )}

            <div className="trustpilot-admin-stats">
                <div className="trustpilot-stat-card">
                    <div className="trustpilot-stat-label">Total Dismissals</div>
                    <div className="trustpilot-stat-value">{stats.total_dismissals}</div>
                </div>
                <div className="trustpilot-stat-card">
                    <div className="trustpilot-stat-label">Unique Users</div>
                    <div className="trustpilot-stat-value">{stats.unique_users}</div>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="trustpilot-admin-form">
                <div className="trustpilot-form-section">
                    <h2>Plugin Configuration</h2>

                    <div className="trustpilot-form-group">
                        <label htmlFor="enabled" className="trustpilot-checkbox-label">
                            <input
                                type="checkbox"
                                id="enabled"
                                name="enabled"
                                checked={settings.enabled}
                                onChange={handleInputChange}
                            />
                            <span>Enable Plugin</span>
                        </label>
                        <p className="trustpilot-field-description">
                            Turn the plugin on or off globally
                        </p>
                    </div>

                    <div className="trustpilot-form-group">
                        <label htmlFor="days_before_expiry">Days Before Expiry</label>
                        <input
                            type="number"
                            id="days_before_expiry"
                            name="days_before_expiry"
                            value={settings.days_before_expiry}
                            onChange={handleInputChange}
                            min="1"
                            max="365"
                            required
                        />
                        <p className="trustpilot-field-description">
                            Show popup when server expires within this many days (1-365)
                        </p>
                    </div>

                    <div className="trustpilot-form-group">
                        <label htmlFor="review_url">Trustpilot Review URL</label>
                        <input
                            type="url"
                            id="review_url"
                            name="review_url"
                            value={settings.review_url}
                            onChange={handleInputChange}
                            placeholder="https://www.trustpilot.com/evaluate/your-business"
                            required
                        />
                        <p className="trustpilot-field-description">
                            The URL where users will be sent to leave a review
                        </p>
                    </div>

                    <div className="trustpilot-form-group">
                        <label htmlFor="api_key">Trustpilot API Key (Optional)</label>
                        <input
                            type="text"
                            id="api_key"
                            name="api_key"
                            value={settings.api_key}
                            onChange={handleInputChange}
                            placeholder="Enter API key for advanced features"
                        />
                        <p className="trustpilot-field-description">
                            Optional API key for advanced Trustpilot integration
                        </p>
                    </div>
                </div>

                <div className="trustpilot-form-actions">
                    <button 
                        type="submit" 
                        className="trustpilot-btn-save"
                        disabled={saving}
                    >
                        {saving ? 'Saving...' : 'Save Settings'}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default AdminSettings;
