import React from 'react';
import '@lottiefiles/lottie-player';

/**
 * Loading overlay component - shows while waiting for response/audio
 * Positioned over the canvas, centered
 */
export function LoadingOverlay({ visible }) {
  if (!visible) return null;

  return (
    <div className="avatar3d-loading-overlay">
      <div className="avatar3d-loading-content">
        {/* Lottie animation loader */}
        <lottie-player
          src="https://lottie.host/8891318b-7fd9-471d-a9f4-e1358fd65cd6/EQt3MHyLWk.json"
          style={{ width: '80px', height: '80px' }}
          loop
          autoplay
          speed="1.4"
          direction="1"
          mode="normal"
        />
        <p className="avatar3d-loading-text">Elaborazione...</p>
      </div>
    </div>
  );
}

export default LoadingOverlay;