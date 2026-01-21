// Main entry point for Trustpilot plugin frontend
// This file exports the main components for use in the PteroCA Panel

export { default as TrustpilotPopup } from './TrustpilotPopup.jsx';
export { default as AdminSettings } from './AdminSettings.jsx';

// Hook to integrate with PteroCA Panel
export const initTrustpilotPlugin = (serverData) => {
    // This function should be called by the panel to initialize the plugin
    // It will check if the popup should be shown and render it if needed
    
    if (!serverData || !serverData.id) {
        console.warn('Trustpilot Plugin: No server data provided');
        return;
    }

    // Create a container for the popup if it doesn't exist
    let container = document.getElementById('trustpilot-popup-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'trustpilot-popup-container';
        document.body.appendChild(container);
    }

    // Import React and ReactDOM dynamically if needed
    // In a real implementation, these would be available from the panel
    if (typeof React !== 'undefined' && typeof ReactDOM !== 'undefined') {
        const { TrustpilotPopup } = require('./TrustpilotPopup.jsx');
        ReactDOM.render(
            React.createElement(TrustpilotPopup, {
                serverId: serverData.id,
                userId: serverData.userId
            }),
            container
        );
    }
};

// Export for admin panel
export const initAdminSettings = () => {
    const container = document.getElementById('trustpilot-admin-root');
    
    if (!container) {
        console.warn('Trustpilot Plugin: Admin container not found');
        return;
    }

    if (typeof React !== 'undefined' && typeof ReactDOM !== 'undefined') {
        const { AdminSettings } = require('./AdminSettings.jsx');
        ReactDOM.render(
            React.createElement(AdminSettings),
            container
        );
    }
};

// Auto-initialize if in admin page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('trustpilot-admin-root')) {
            initAdminSettings();
        }
    });
} else {
    if (document.getElementById('trustpilot-admin-root')) {
        initAdminSettings();
    }
}
