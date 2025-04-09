<?php
// This is a simple PHP script to serve the chatbot frontend.
include '../includes/config.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pharmacy Chat Assistant</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css" />
  <style>
    :root {
      --primary-color: #4caf50; /* Pharmacy green */
      --secondary-color: #ffffff;
      --accent-color: #f44336;
      --background: #f5f5f5;
      --chat-bg: #ffffff;
      --chat-header-bg: #4caf50;
      --chat-header-color: #ffffff;
      --message-bg: #ffffff;
      --bot-message-bg: #f9f9f9;
      --error-color: #ff4444;
      --border-radius: 8px;
    }

    /* Global resets */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background: var(--background);
      min-height: 100vh;
      position: relative;
    }

    /* Maximized Chat Container (fixed width/height) */
    .chat-container {
      position: fixed;
      bottom: 80px;
      right: 20px;
      width: 400px;
      max-height: 800px;
      background: var(--chat-bg);
      border-radius: var(--border-radius);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: all 0.3s ease;
      z-index: 1000;
    }

    /* Chat Header */
    .chat-header {
      background: var(--chat-header-bg);
      color: var(--chat-header-color);
      padding: 14px 16px;
      font-size: 1.15em;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s ease;
    }
    .chat-header .header-title {
      flex-grow: 1;
      margin-left: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    /* Pharmacy-themed header icon */
    .chat-header .header-title i {
      font-size: 1.3em;
    }
    .chat-header .minimize-button {
      background: none;
      border: none;
      color: var(--chat-header-color);
      font-size: 1.2em;
      cursor: pointer;
      transition: transform 0.2s ease;
    }
    .chat-header .minimize-button:hover {
      transform: scale(1.1);
    }

    /* Chat Body */
    .chat-body {
      display: flex;
      flex-direction: column;
      flex-grow: 1;
      overflow: hidden;
    }
    #chatMessages {
      flex-grow: 1;
      padding: 16px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 12px;
      background: var(--chat-bg);
    }
    .message {
      max-width: 80%;
      padding: 12px 16px;
      border-radius: 20px;
      animation: fadeIn 0.3s ease;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      word-wrap: break-word;
      line-height: 1.4;
    }
    .user-message {
      background: var(--primary-color);
      color: var(--secondary-color);
      align-self: flex-end;
      border-radius: 20px 20px 0 20px;
    }
    .bot-message {
      background: var(--bot-message-bg);
      color: #333;
      align-self: flex-start;
      border-radius: 20px 20px 20px 0;
    }

    /* Input Area */
    .input-container {
      padding: 10px 14px;
      border-top: 1px solid #ddd;
      display: flex;
      gap: 8px;
      align-items: center;
      background: var(--chat-bg);
      flex-shrink: 0;
    }
    #userInput {
      flex: 1;
      padding: 10px 14px;
      border: 1px solid #ddd;
      border-radius: 20px;
      resize: none;
      min-height: 40px;
      max-height: 120px;
      font-size: 0.95em;
      line-height: 1.4;
      transition: all 0.3s ease;
    }
    #userInput:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    }
    .action-buttons {
      display: flex;
      gap: 6px;
      flex-shrink: 0;
    }
    .action-button {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--primary-color);
      font-size: 1.1em;
      padding: 6px;
      transition: all 0.2s ease;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    .action-button:hover {
      background: rgba(76, 175, 80, 0.1);
      transform: scale(1.1);
    }
    .action-button:hover::after {
      content: attr(title);
      position: absolute;
      bottom: -28px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0, 0, 0, 0.8);
      color: #fff;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.75rem;
      white-space: nowrap;
    }

    /* File Preview & Status */
    .file-preview { 
      max-width: 300px; 
      border-radius: 6px; 
      margin: 0 20px 10px; 
      display: none; 
    }
    .file-name { 
      max-width: 120px; 
      white-space: nowrap; 
      overflow: hidden; 
      text-overflow: ellipsis; 
      font-size: 0.85em; 
      background: rgba(0, 0, 0, 0.05); 
      padding: 4px 8px; 
      border-radius: 12px; 
      display: none; 
    }
    .loading-spinner {
      display: none;
      border: 3px solid #f3f3f3;
      border-top: 3px solid var(--primary-color);
      border-radius: 50%;
      width: 20px;
      height: 20px;
      animation: spin 1s linear infinite;
    }
    .status-message {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0, 0, 0, 0.8);
      color: #fff;
      padding: 10px 20px;
      border-radius: 25px;
      display: none;
      animation: slideUp 0.3s ease;
      z-index: 1100;
    }

    /* Floating Chat Toggle Button (Pharmacy Icon) */
    .chat-toggle-button {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: var(--chat-header-bg);
      color: var(--chat-header-color);
      border: none;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      cursor: pointer;
      z-index: 1100;
      transition: background 0.3s;
    }
    /* Using a pharmacy-themed icon (mortar-pestle) */
    .chat-toggle-button i {
      font-size: 1.8em;
    }
    .chat-toggle-button:hover { 
      background: #45a049; 
    }

    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    @keyframes slideUp {
      from { transform: translate(-50%, 20px); opacity: 0; }
      to { transform: translate(-50%, 0); opacity: 1; }
    }

    /* Minimized State: Show only the header icon */
    .chat-container.minimized {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      bottom: 20px;
      right: 20px;
      transition: all 0.3s ease;
    }
    .chat-container.minimized .chat-body,
    .chat-container.minimized .input-container {
      display: none;
    }
    .chat-container.minimized .chat-header {
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .chat-container.minimized .header-title {
      display: none;
    }

    /* Responsive Adjustments */
    @media (max-width: 600px) {
      .chat-container {
        width: 90%;
        right: 5%;
        bottom: 80px;
        max-height: 80%;
      }
      .chat-toggle-button { width: 50px; height: 50px; }
      .chat-container.minimized {
        width: 50px;
        height: 50px;
      }
    }
  </style>
</head>
<body>
  <!-- Language selection remains unchanged -->
  <select id="language-select" style="margin: 10px;">
    <option value="en-US">English (US)</option>
    <option value="hi-IN">HINDI</option>
    <option value="fr-FR">French</option>
    <option value="de-DE">German</option>
    <option value="it-IT">Italian</option>
    <option value="ja-JP">Japanese</option>
    <option value="ko-KR">Korean</option>
    <option value="pt-BR">Portuguese (Brazil)</option>
    <option value="ru-RU">Russian</option>
    <option value="ne-NP">NEPALI</option>
    <option value="zh-TW">Chinese (Traditional)</option>
  </select>

  <!-- Chat container with header and body -->
  <div class="chat-container" id="chatContainer">
    <div class="chat-header" id="chatHeader">
      <span class="header-title"><i class="fas fa-mortar-pestle"></i> Pharmacy Chat</span>
      <button class="minimize-button" id="minimizeButton" title="Minimize">
        <i class="fas fa-window-minimize"></i>
      </button>
    </div>
    <div class="chat-body">
      <div id="chatMessages"></div>
      <img id="imagePreview" class="file-preview" alt="Preview">
      <div class="input-container">
        <div class="action-buttons">
          <label for="fileInput" class="action-button" title="Attach image">
            <i class="fas fa-paperclip"></i>
          </label>
          <input type="file" id="fileInput" hidden accept="image/*">
          <span class="file-name" id="fileName"></span>
          <button class="action-button" id="voiceButton" title="Voice Input">
            <i class="fas fa-microphone"></i>
          </button>
          <button class="action-button" id="toggleVoice" title="Toggle Speech">
            <i class="fas fa-volume-mute"></i>
          </button>
          <button class="action-button" id="clearChat" title="Clear Chat">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
        <textarea id="userInput" placeholder="Type or speak your message"></textarea>
        <button class="action-button" id="sendButton" title="Send">
          <i class="fas fa-paper-plane"></i>
        </button>
        <div class="loading-spinner" id="loadingSpinner"></div>
      </div>
    </div>
    <div class="status-message" id="statusMessage"></div>
  </div>

  <!-- Floating chat toggle button with pharmacy icon -->
  <button class="chat-toggle-button" id="chatToggleButton" title="Toggle Chat">
    <i class="fas fa-mortar-pestle"></i>
  </button>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const chatContainer = document.getElementById('chatContainer');
      const chatMessages = document.getElementById('chatMessages');
      const userInput = document.getElementById('userInput');
      const fileInput = document.getElementById('fileInput');
      const loadingSpinner = document.getElementById('loadingSpinner');
      const statusMessage = document.getElementById('statusMessage');
      const select = document.getElementById('language-select');
      select.addEventListener('change', () => {
        localStorage.setItem('language', select.value);
      });

      // API configuration for Gemini
      const API_KEY = 'AIzaSyCbb67pP3aNu30Yt4WrUTBhto00YNjLy0k';
      const MODEL_NAME = 'gemini-1.5-flash';
      let recognition;
      let isListening = false;
      let silenceTimer;
      const SILENCE_TIMEOUT = 2000;
      let attachedFile = null;
      let isVoiceEnabled = false;

      // Function to check for pharmacy info queries
      function checkPharmacyInfo(message) {
        const lowerMsg = message.toLowerCase();
        if (lowerMsg.includes("pharmacy info") ||
            lowerMsg.includes("about pharmacy") ||
            lowerMsg.includes("pharmacy details") ||
            lowerMsg.includes("pharmacy services") ||
            lowerMsg.includes("pharmacy hours") ||
            lowerMsg.includes("pharmacy location") ||
            lowerMsg.includes("pharmacy contact")) {
          // Extended pharmacy info (customize as needed)
          return "Welcome to PharmaCare – your trusted partner in healthcare! We specialize in providing prescription medications, over-the-counter drugs, health supplements, personal care products, and medical devices. Our state-of-the-art facility at itahari-5,sunsari, is equipped with modern technology to ensure your safety and satisfaction. Our licensed pharmacists are available for professional consultations, prescription refills, and personalized advice. In addition, we offer online consultations, home delivery services, and exclusive discounts for seniors and students. For further details, visit our website or call us at 9815760082. Thank you for choosing Pharmacare for your health and wellness needs. We look forward to serving you!.  We are open from 9 AM to 8 PM, Monday to Saturday. Our location is at itahari-5, sunsari. You can reach us at 9815760082. We also offer online consultations and home delivery services for your convenience. Thank you for choosing PharmaCare!";
        } else if (lowerMsg.includes("pharmacy hours") ||
                   lowerMsg.includes("pharmacy location") ||
                   lowerMsg.includes("pharmacy contact")) {
            return "We are open from 9 AM to 8 PM, Monday to Saturday. Our location is at itahari-5, sunsari. You can reach us at 9815760082. We also offer online consultations and home delivery services for your convenience. Thank you for choosing PharmaCare!";
            
        }

        return null;
      }

      // Initialize voice recognition
      function initVoiceRecognition() {
        if ('webkitSpeechRecognition' in window) {
          recognition = new webkitSpeechRecognition();
          recognition.continuous = true;
          recognition.interimResults = true;
          recognition.lang = localStorage.getItem('language') || 'en-US';

          recognition.onstart = () => {
            isListening = true;
            document.getElementById('voiceButton').classList.add('active');
            showStatus('Listening...');
          };

          recognition.onresult = (event) => {
            clearTimeout(silenceTimer);
            const transcript = Array.from(event.results)
              .map(result => result[0])
              .map(result => result.transcript)
              .join('');
            userInput.value = transcript;
            silenceTimer = setTimeout(() => {
              if (event.results[0] && !event.results[0].isFinal) {
                handleSendMessage();
              }
            }, SILENCE_TIMEOUT);
            if (event.results[0] && event.results[0].isFinal) {
              handleSendMessage();
            }
          };

          recognition.onerror = (event) => {
            showStatus(`Error: ${event.error}`, true);
            resetVoiceState();
          };

          recognition.onend = resetVoiceState;
        } else {
          showStatus("Voice recognition not supported", true);
          document.getElementById('voiceButton').disabled = true;
        }
      }

      async function handleSendMessage() {
        const message = userInput.value.trim();
        if (!message && !attachedFile) return;

        // Check if the user is asking for pharmacy details
        const pharmacyInfo = checkPharmacyInfo(message);
        if (pharmacyInfo) {
          addMessage(message, true);
          addMessage(pharmacyInfo, false);
          userInput.value = '';
          resetFileInput();
          return;
        }

        loadingSpinner.style.display = 'block';
        document.getElementById('sendButton').disabled = true;
        try {
          const contentParts = [];
          const finalMessage = message || "Describe this image";
          if (message) contentParts.push({ text: finalMessage });
          if (attachedFile) {
            const base64Data = await readFileContent(attachedFile);
            contentParts.push({
              inline_data: {
                mime_type: attachedFile.type,
                data: base64Data.split(',')[1]
              }
            });
          }
          addMessage(finalMessage, true);
          const botResponse = await getGeminiResponse(contentParts);
          addMessage(botResponse, false);
          userInput.value = '';
          resetFileInput();
        } catch (error) {
          console.error('Error:', error);
          showStatus(error.message, true);
          addMessage(`Error: ${error.message}`, false);
        } finally {
          loadingSpinner.style.display = 'none';
          document.getElementById('sendButton').disabled = false;
          resetVoiceState();
        }
      }

      async function getGeminiResponse(contentParts) {
        const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${MODEL_NAME}:generateContent`;
        try {
          const response = await fetch(`${API_URL}?key=${API_KEY}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
              contents: [{
                role: "user",
                parts: contentParts
              }]
            })
          });
          if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error.message || 'API request failed');
          }
          const data = await response.json();
          if (!data.candidates?.[0]?.content?.parts) {
            throw new Error('Invalid response format from API');
          }
          return data.candidates[0].content.parts[0].text;
        } catch (error) {
          console.error('API Error:', error);
          throw new Error(`API Error: ${error.message}`);
        }
      }

      function toggleTextToSpeech() {
        if (!('speechSynthesis' in window)) {
          showStatus("Text-to-speech not supported", true);
          return;
        }
        isVoiceEnabled = !isVoiceEnabled;
        document.getElementById('toggleVoice').innerHTML =
          `<i class="fas fa-volume-${isVoiceEnabled ? 'up' : 'mute'}"></i>`;
        if (!isVoiceEnabled) {
          window.speechSynthesis.cancel();
        }
      }

      function speakMessage(message) {
        if (isVoiceEnabled) {
          const speech = new SpeechSynthesisUtterance(message);
          speech.rate = 1.0;
          speech.pitch = 1.0;
          window.speechSynthesis.speak(speech);
        }
      }

      function addMessage(content, isUser) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        if (content.startsWith('data:image')) {
          const img = document.createElement('img');
          img.src = content;
          img.className = 'file-preview';
          messageDiv.appendChild(img);
        } else if (content.includes('```')) {
          const parts = content.split('```');
          parts.forEach((part, index) => {
            if (index % 2 === 0) {
              messageDiv.appendChild(document.createTextNode(part));
            } else {
              const pre = document.createElement('pre');
              const code = document.createElement('code');
              code.textContent = part;
              pre.appendChild(code);
              messageDiv.appendChild(pre);
              hljs.highlightElement(code);
            }
          });
        } else {
          messageDiv.textContent = content;
        }
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        if (!isUser) {
          speakMessage(content);
        }
      }

      function resetVoiceState() {
        isListening = false;
        document.getElementById('voiceButton').classList.remove('active');
        clearTimeout(silenceTimer);
      }

      function resetFileInput() {
        fileInput.value = '';
        document.getElementById('fileName').textContent = '';
        document.getElementById('fileName').style.display = 'none';
        document.getElementById('imagePreview').style.display = 'none';
        attachedFile = null;
      }

      function clearChatHistory() {
        chatMessages.innerHTML = '';
        resetFileInput();
        userInput.value = '';
        showStatus('Chat history cleared');
      }

      function readFileContent(file) {
        return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.onload = () => resolve(reader.result);
          reader.onerror = reject;
          reader.readAsDataURL(file);
        });
      }

      function showStatus(text, isError = false) {
        statusMessage.textContent = text;
        statusMessage.style.backgroundColor = isError ? 'var(--error-color)' : 'rgba(0,0,0,0.8)';
        statusMessage.style.display = 'block';
        setTimeout(() => statusMessage.style.display = 'none', 3000);
      }

      // Event listeners for voice, file input, etc.
      document.getElementById('voiceButton').addEventListener('click', () => {
        if (!isListening) {
          initVoiceRecognition();
          recognition.start();
        } else {
          recognition.stop();
        }
      });
      document.getElementById('fileInput').addEventListener('change', async (e) => {
        try {
          attachedFile = e.target.files[0];
          if (!attachedFile) return;
          if (!attachedFile.type.startsWith('image/')) {
            throw new Error('Only image files are supported (PNG, JPG, JPEG)');
          }
          document.getElementById('fileName').textContent = attachedFile.name;
          document.getElementById('fileName').style.display = 'inline-block';
          const preview = document.getElementById('imagePreview');
          preview.src = await readFileContent(attachedFile);
          preview.style.display = 'block';
        } catch (error) {
          showStatus(error.message, true);
          resetFileInput();
        }
      });
      document.getElementById('sendButton').addEventListener('click', handleSendMessage);
      document.getElementById('clearChat').addEventListener('click', clearChatHistory);
      document.getElementById('toggleVoice').addEventListener('click', toggleTextToSpeech);
      userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          handleSendMessage();
        }
      });
      hljs.highlightAll();

      // Chat toggle functionality (minimize/maximize)
      const chatToggleButton = document.getElementById('chatToggleButton');
      const minimizeButton = document.getElementById('minimizeButton');
      function toggleChat() {
        chatContainer.classList.toggle('minimized');
      }
      chatToggleButton.addEventListener('click', toggleChat);
      minimizeButton.addEventListener('click', toggleChat);
    });
  </script>
</body>
</html>
