import React from 'react';

/**
 * Status indicator component showing processing state
 */
export function StatusIndicator({ isActive }) {
  return (
    <div className="avatar3d-status">
      <span className={`avatar3d-status-dot ${isActive ? 'active' : ''}`}></span>
      <span>{isActive ? 'In elaborazione...' : 'Pronto'}</span>
    </div>
  );
}

export default StatusIndicator;
