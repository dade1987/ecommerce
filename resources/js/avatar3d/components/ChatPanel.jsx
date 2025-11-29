import React from 'react';

/**
 * Chat message list component
 */
function ChatMessages({ chats, loading, speaking, playing }) {
  return (
    <>
      {chats.map((chat, index) => (
        <p key={index} className={chat.who}>
          {chat.msg}
          {chat.who === 'bot' && chat.exct !== '0' && (
            <span className='time'>{"generato in " + chat.exct + "s"}</span>
          )}
        </p>
      ))}

      {(loading || speaking) && !playing && (
        <p className="loading">
          <span className="loading-dots">
            <span>.</span><span>.</span><span>.</span>
          </span>
        </p>
      )}
    </>
  );
}

/**
 * Microphone icon SVG
 */
function MicIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
      <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
      <line x1="12" x2="12" y1="19" y2="22"></line>
    </svg>
  );
}

/**
 * Send icon SVG
 */
function SendIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m22 2-7 20-4-9-9-4Z"></path>
      <path d="M22 2 11 13"></path>
    </svg>
  );
}

/**
 * Full chat panel component for normal mode
 */
export function ChatPanel({
  title,
  chats,
  message,
  onMessageChange,
  onSend,
  loading,
  speaking,
  playing,
  enableSpeechRecognition,
  onMicDown,
  onMicUp,
  chatBoxRef,
}) {
  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      onSend();
    }
  };

  return (
    <div className='avatar3d-chat-div'>
      <div className='avatar3d-chat-header'>
        <h2>{title}</h2>
      </div>

      <div className='avatar3d-chat-box' ref={chatBoxRef}>
        <ChatMessages
          chats={chats}
          loading={loading}
          speaking={speaking}
          playing={playing}
        />
      </div>

      <div className='avatar3d-msg-box'>
        {enableSpeechRecognition && (
          <button
            className='avatar3d-msgbtn'
            id='mic'
            onTouchStart={onMicDown}
            onMouseDown={onMicDown}
            onTouchEnd={onMicUp}
            onMouseUp={onMicUp}
          >
            <MicIcon />
          </button>
        )}

        <input
          type='text'
          value={message}
          onChange={e => onMessageChange(e.target.value)}
          onKeyDown={handleKeyDown}
          placeholder='Scrivi un messaggio...'
        />

        <button
          className='avatar3d-msgbtn'
          id='send'
          onClick={onSend}
        >
          <SendIcon />
        </button>
      </div>
    </div>
  );
}

export default ChatPanel;
