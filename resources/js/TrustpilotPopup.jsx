import React, { useState, useEffect } from 'react';
import axios from 'axios';
import './TrustpilotPopup.css';

const TrustpilotPopup = ({ serverId, userId }) => {
    const [showPopup, setShowPopup] = useState(false);
    const [reviewUrl, setReviewUrl] = useState('');
    const [daysUntilExpiry, setDaysUntilExpiry] = useState(0);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        checkIfShouldShow();
    }, [serverId]);

    const checkIfShouldShow = async () => {
        try {
            const response = await axios.get(`/api/trustpilot/check/${serverId}`);
            const data = response.data;

            if (data.show) {
                setShowPopup(true);
                setReviewUrl(data.review_url);
                setDaysUntilExpiry(data.days_until_expiry);
            }
        } catch (error) {
            console.error('Error checking Trustpilot popup status:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleReviewClick = () => {
        window.open(reviewUrl, '_blank', 'noopener,noreferrer');
        handleDismiss();
    };

    const handleDismiss = async () => {
        try {
            await axios.post(`/api/trustpilot/dismiss/${serverId}`);
            setShowPopup(false);
        } catch (error) {
            console.error('Error dismissing Trustpilot popup:', error);
        }
    };

    if (loading || !showPopup) {
        return null;
    }

    return (
        <div className="trustpilot-overlay">
            <div className="trustpilot-popup">
                <div className="trustpilot-popup-header">
                    <h3>We'd love your feedback</h3>
                    <button 
                        className="trustpilot-close-btn" 
                        onClick={handleDismiss}
                        aria-label="Close"
                    >
                        &times;
                    </button>
                </div>
                <div className="trustpilot-popup-body">
                    <p>
                        Your server expires in {daysUntilExpiry} {daysUntilExpiry === 1 ? 'day' : 'days'}. 
                        Before it goes, would you mind sharing your experience with us?
                    </p>
                    <p className="trustpilot-popup-note">
                        Your feedback helps us improve our services for everyone.
                    </p>
                </div>
                <div className="trustpilot-popup-footer">
                    <button 
                        className="trustpilot-btn trustpilot-btn-secondary" 
                        onClick={handleDismiss}
                    >
                        No thanks
                    </button>
                    <button 
                        className="trustpilot-btn trustpilot-btn-primary" 
                        onClick={handleReviewClick}
                    >
                        Leave a Review
                    </button>
                </div>
            </div>
        </div>
    );
};

export default TrustpilotPopup;
