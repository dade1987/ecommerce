#!/usr/bin/env node

/**
 * Azure TTS HTTP Server with Viseme support
 * Exposes /talk endpoint for PHP to call via HTTP
 */

const express = require('express');
const sdk = require('microsoft-cognitiveservices-speech-sdk');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const app = express();
app.use(express.json());

// Configuration from environment
const SPEECH_KEY = process.env.AZURE_SPEECH_KEY;
const SPEECH_REGION = process.env.AZURE_SPEECH_REGION || 'italynorth';
const DEFAULT_VOICE = process.env.AZURE_DEFAULT_VOICE || 'it-IT-ElsaNeural';
const AUDIO_DIR = process.env.AUDIO_DIR || '/app/audio';
const PORT = process.env.PORT || 3001;

// Ensure audio directory exists
if (!fs.existsSync(AUDIO_DIR)) {
    fs.mkdirSync(AUDIO_DIR, { recursive: true });
}

// Blendshape names (ARKit compatible)
const blendShapeNames = [
    "eyeBlinkLeft", "eyeLookDownLeft", "eyeLookInLeft", "eyeLookOutLeft", "eyeLookUpLeft",
    "eyeSquintLeft", "eyeWideLeft", "eyeBlinkRight", "eyeLookDownRight", "eyeLookInRight",
    "eyeLookOutRight", "eyeLookUpRight", "eyeSquintRight", "eyeWideRight", "jawForward",
    "jawLeft", "jawRight", "jawOpen", "mouthClose", "mouthFunnel", "mouthPucker",
    "mouthLeft", "mouthRight", "mouthSmileLeft", "mouthSmileRight", "mouthFrownLeft",
    "mouthFrownRight", "mouthDimpleLeft", "mouthDimpleRight", "mouthStretchLeft",
    "mouthStretchRight", "mouthRollLower", "mouthRollUpper", "mouthShrugLower",
    "mouthShrugUpper", "mouthPressLeft", "mouthPressRight", "mouthLowerDownLeft",
    "mouthLowerDownRight", "mouthUpperUpLeft", "mouthUpperUpRight", "browDownLeft",
    "browDownRight", "browInnerUp", "browOuterUpLeft", "browOuterUpRight", "cheekPuff",
    "cheekSquintLeft", "cheekSquintRight", "noseSneerLeft", "noseSneerRight", "tongueOut",
    "headRoll", "leftEyeRoll", "rightEyeRoll"
];

/**
 * Synthesize text to speech with viseme data
 */
async function synthesize(text, voice, outputFile) {
    return new Promise((resolve, reject) => {
        if (!SPEECH_KEY) {
            return reject(new Error('AZURE_SPEECH_KEY not configured'));
        }

        const speechConfig = sdk.SpeechConfig.fromSubscription(SPEECH_KEY, SPEECH_REGION);
        speechConfig.speechSynthesisOutputFormat = sdk.SpeechSynthesisOutputFormat.Audio16Khz32KBitRateMonoMp3;

        const audioConfig = sdk.AudioConfig.fromAudioFileOutput(outputFile);
        const synthesizer = new sdk.SpeechSynthesizer(speechConfig, audioConfig);

        // Extract language from voice name
        const langMatch = voice.match(/^([a-z]{2}-[A-Z]{2})/);
        const lang = langMatch ? langMatch[1] : 'it-IT';

        // SSML with viseme request
        const ssml = `<speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis" xmlns:mstts="http://www.w3.org/2001/mstts" xml:lang="${lang}">
<voice name="${voice}">
  <mstts:viseme type="FacialExpression"/>
  ${text}
</voice>
</speak>`;

        let blendData = [];
        let timeStep = 1 / 60;
        let timeStamp = 0;

        // Collect viseme data
        synthesizer.visemeReceived = (s, e) => {
            try {
                const animation = JSON.parse(e.animation);

                animation.BlendShapes.forEach(blendArray => {
                    let blend = {};
                    blendShapeNames.forEach((shapeName, i) => {
                        blend[shapeName] = blendArray[i] || 0;
                    });

                    blendData.push({
                        time: timeStamp,
                        blendshapes: blend
                    });
                    timeStamp += timeStep;
                });
            } catch (err) {
                // Ignore parse errors for viseme data
            }
        };

        let synthesisComplete = false;

        synthesizer.synthesisCompleted = () => {
            synthesisComplete = true;
        };

        synthesizer.SynthesisCanceled = (s, e) => {
            synthesizer.close();
            reject(new Error(`Synthesis canceled: ${e.errorDetails}`));
        };

        // Start synthesis
        synthesizer.speakSsmlAsync(ssml);

        // Poll for completion
        let lastSize = 0;
        let stableCount = 0;

        const checkFile = setInterval(() => {
            try {
                if (fs.existsSync(outputFile)) {
                    const stats = fs.statSync(outputFile);
                    if (stats.size > 0 && stats.size === lastSize) {
                        stableCount++;
                        if (synthesisComplete && stableCount >= 10) {
                            clearInterval(checkFile);
                            synthesizer.close();
                            resolve({ blendData });
                        }
                    } else {
                        lastSize = stats.size;
                        stableCount = 0;
                    }
                }
            } catch (err) {
                // Continue polling
            }
        }, 100);

        // Timeout after 60 seconds
        setTimeout(() => {
            clearInterval(checkFile);
            synthesizer.close();
            reject(new Error('Synthesis timeout'));
        }, 60000);
    });
}

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ status: 'ok', service: 'avatar3d-tts' });
});

// TTS endpoint
app.post('/talk', async (req, res) => {
    try {
        const { text, voice = DEFAULT_VOICE } = req.body;

        if (!text) {
            return res.status(400).json({ error: 'Text is required' });
        }

        // Generate unique filename
        const randomId = crypto.randomBytes(4).toString('hex');
        const filename = `speech-${randomId}.mp3`;
        const outputFile = path.join(AUDIO_DIR, filename);

        console.log(`[TTS] Synthesizing: "${text.substring(0, 50)}..." with voice ${voice}`);

        const result = await synthesize(text, voice, outputFile);

        console.log(`[TTS] Generated ${filename} with ${result.blendData.length} blend frames`);

        res.json({
            blendData: result.blendData,
            filename: `/avatar3d/audio/${filename}`
        });

    } catch (error) {
        console.error('[TTS] Error:', error.message);
        res.status(500).json({ error: error.message });
    }
});

// Serve audio files
app.use('/avatar3d/audio', express.static(AUDIO_DIR));

// Cleanup endpoint
app.post('/cleanup', (req, res) => {
    const maxAge = req.body.maxAge || 3600; // 1 hour default
    let deleted = 0;

    try {
        const files = fs.readdirSync(AUDIO_DIR);
        const now = Date.now();

        files.forEach(file => {
            if (file.startsWith('speech-') && file.endsWith('.mp3')) {
                const filePath = path.join(AUDIO_DIR, file);
                const stats = fs.statSync(filePath);
                const age = (now - stats.mtimeMs) / 1000;

                if (age > maxAge) {
                    fs.unlinkSync(filePath);
                    deleted++;
                }
            }
        });

        res.json({ success: true, deleted });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
    console.log(`[TTS] Azure TTS Server running on port ${PORT}`);
    console.log(`[TTS] Region: ${SPEECH_REGION}, Default voice: ${DEFAULT_VOICE}`);
    console.log(`[TTS] Audio directory: ${AUDIO_DIR}`);
    if (!SPEECH_KEY) {
        console.warn('[TTS] WARNING: AZURE_SPEECH_KEY not set!');
    }
});