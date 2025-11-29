import React, { useState } from 'react';

/**
 * Close icon SVG
 */
function CloseIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
    </svg>
  );
}

/**
 * Chat bubble icon SVG
 */
function ChatIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
  );
}

/**
 * Send icon SVG (smaller)
 */
function SendIconSmall() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
    </svg>
  );
}

/**
 * Widget mode chat component with toggle and mini chat
 */
export function WidgetChat({
  chats,
  message,
  onMessageChange,
  onSend,
  loading,
  speaking,
  playing,
}) {
  const [showPanel, setShowPanel] = useState(false);

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      onSend();
    }
  };

  return (
    <>
      {/* Toggle button */}
      <button
        className="avatar3d-widget-toggle"
        onClick={() => setShowPanel(!showPanel)}
      >
        {showPanel ? <CloseIcon /> : <ChatIcon />}
      </button>

      {/* Chat panel overlay */}
      {showPanel && (
        <div className='avatar3d-widget-chat'>
          <div className='avatar3d-chat-box'>
            {chats.map((chat, index) => (
              <p key={index} className={chat.who}>
                {chat.msg}
              </p>
            ))}
            {(loading || speaking) && !playing && (
              <p className="loading">
                <span className="loading-dots">
                  <span>.</span><span>.</span><span>.</span>
                </span>
              </p>
            )}
          </div>
        </div>
      )}

      {/* Input always visible at bottom */}
      <div className='avatar3d-widget-input'>
        <input
          type='text'
          value={message}
          onChange={e => onMessageChange(e.target.value)}
          onKeyDown={handleKeyDown}
          placeholder='Scrivi...'
        />
        <button onClick={onSend}>
          <SendIconSmall />
        </button>
      </div>
    </>
  );
}

export default WidgetChat;
