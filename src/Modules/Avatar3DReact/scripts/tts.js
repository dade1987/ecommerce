#!/usr/bin/env node

/**
 * Azure TTS with Viseme support
 * Called from Laravel via Process
 *
 * Usage: node tts.js --text "Hello" --voice "it-IT-ElsaNeural" --output "/path/to/output.mp3" --key "xxx" --region "xxx"
 */

const sdk = require('microsoft-cognitiveservices-speech-sdk');
const fs = require('fs');

// Parse command line arguments
const args = {};
for (let i = 2; i < process.argv.length; i += 2) {
    const key = process.argv[i].replace('--', '');
    const value = process.argv[i + 1];
    args[key] = value;
}

const { text, voice, output, key, region } = args;

if (!text || !output || !key || !region) {
    console.error(JSON.stringify({ error: 'Missing required arguments' }));
    process.exit(1);
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

// Extract language from voice name (e.g., "it-IT-ElsaNeural" -> "it-IT")
const voiceName = voice || 'it-IT-ElsaNeural';
const langMatch = voiceName.match(/^([a-z]{2}-[A-Z]{2})/);
const lang = langMatch ? langMatch[1] : 'it-IT';

// SSML template
const ssml = `<speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis" xmlns:mstts="http://www.w3.org/2001/mstts" xml:lang="${lang}">
<voice name="${voiceName}">
  <mstts:viseme type="FacialExpression"/>
  ${text}
</voice>
</speak>`;

// Main synthesis function
async function synthesize() {
    return new Promise((resolve, reject) => {
        const speechConfig = sdk.SpeechConfig.fromSubscription(key, region);
        speechConfig.speechSynthesisOutputFormat = sdk.SpeechSynthesisOutputFormat.Audio16Khz32KBitRateMonoMp3;

        const audioConfig = sdk.AudioConfig.fromAudioFileOutput(output);
        const synthesizer = new sdk.SpeechSynthesizer(speechConfig, audioConfig);

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
            reject(new Error(`Synthesis canceled: ${e.errorDetails}`));
        };

        // Start synthesis
        synthesizer.speakSsmlAsync(ssml);

        // Poll for completion
        let lastSize = 0;
        let stableCount = 0;

        const checkFile = setInterval(() => {
            try {
                if (fs.existsSync(output)) {
                    const stats = fs.statSync(output);
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

// Run and output JSON result
synthesize()
    .then(result => {
        console.log(JSON.stringify(result));
        process.exit(0);
    })
    .catch(err => {
        console.error(JSON.stringify({ error: err.message }));
        process.exit(1);
    });