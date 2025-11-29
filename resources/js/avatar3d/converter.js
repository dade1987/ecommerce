import {
	AnimationClip,
	BooleanKeyframeTrack,
	ColorKeyframeTrack,
	NumberKeyframeTrack,
	Vector3,
	VectorKeyframeTrack
} from 'three';

var fps = 60

// Mapping from Azure/ARKit blendshape names to model morph target names
// This mapping tries multiple possible names for each blendshape
const arkitToCCBaseMapping = {
  // Brow
  'browInnerUp': ['A01_Brow_Inner_Up', 'Brow_Raise_Inner_L', 'Brow_Raise_Inner_R', 'Brow_Raise_Inner_Left', 'Brow_Raise_Inner_Right'],
  'browDownLeft': ['A02_Brow_Down_Left', 'Brow_Drop_L', 'Brow_Drop_Left'],
  'browDownRight': ['A03_Brow_Down_Right', 'Brow_Drop_R', 'Brow_Drop_Right'],
  'browOuterUpLeft': ['A04_Brow_Outer_Up_Left', 'Brow_Raise_Outer_L', 'Brow_Raise_Outer_Left'],
  'browOuterUpRight': ['A05_Brow_Outer_Up_Right', 'Brow_Raise_Outer_R', 'Brow_Raise_Outer_Right'],

  // Eyes
  'eyeLookUpLeft': ['A06_Eye_Look_Up_Left', 'Eye_L_Look_Up'],
  'eyeLookUpRight': ['A07_Eye_Look_Up_Right', 'Eye_R_Look_Up'],
  'eyeLookDownLeft': ['A08_Eye_Look_Down_Left', 'Eye_L_Look_Down'],
  'eyeLookDownRight': ['A09_Eye_Look_Down_Right', 'Eye_R_Look_Down'],
  'eyeLookOutLeft': ['A10_Eye_Look_Out_Left', 'Eye_L_Look_L'],
  'eyeLookInLeft': ['A11_Eye_Look_In_Left', 'Eye_L_Look_R'],
  'eyeLookInRight': ['A12_Eye_Look_In_Right', 'Eye_R_Look_L'],
  'eyeLookOutRight': ['A13_Eye_Look_Out_Right', 'Eye_R_Look_R'],
  'eyeBlinkLeft': ['A14_Eye_Blink_Left', 'Eye_Blink_L'],
  'eyeBlinkRight': ['A15_Eye_Blink_Right', 'Eye_Blink_R'],
  'eyeSquintLeft': ['A16_Eye_Squint_Left', 'Eye_Squint_L'],
  'eyeSquintRight': ['A17_Eye_Squint_Right', 'Eye_Squint_R'],
  'eyeWideLeft': ['A18_Eye_Wide_Left', 'Eye_Wide_L'],
  'eyeWideRight': ['A19_Eye_Wide_Right', 'Eye_Wide_R'],

  // Cheek
  'cheekPuff': ['A20_Cheek_Puff', 'Cheek_Puff_L', 'Cheek_Puff_R', 'Cheek_Blow_L', 'Cheek_Blow_R'],
  'cheekSquintLeft': ['A21_Cheek_Squint_Left', 'Cheek_Raise_L'],
  'cheekSquintRight': ['A22_Cheek_Squint_Right', 'Cheek_Raise_R'],

  // Nose
  'noseSneerLeft': ['A23_Nose_Sneer_Left', 'Nose_Sneer_L', 'Nose_Flank_Raise_L'],
  'noseSneerRight': ['A24_Nose_Sneer_Right', 'Nose_Sneer_R', 'Nose_Flank_Raise_R'],

  // Jaw
  'jawOpen': ['A25_Jaw_Open', 'Jaw_Open', 'Mouth_Open', 'Merged_Open_Mouth'],
  'jawForward': ['A26_Jaw_Forward', 'Jaw_Forward'],
  'jawLeft': ['A27_Jaw_Left', 'Jaw_L'],
  'jawRight': ['A28_Jaw_Right', 'Jaw_R'],

  // Mouth
  'mouthFunnel': ['A29_Mouth_Funnel', 'Mouth_Funnel_Up_L', 'Mouth_Funnel_Up_R', 'Mouth_Pucker_Open', 'Mouth_Pucker_Up_L'],
  'mouthPucker': ['A30_Mouth_Pucker', 'Mouth_Pucker_Up_L', 'Mouth_Pucker_Up_R', 'Mouth_Pucker'],
  'mouthLeft': ['A31_Mouth_Left', 'Mouth_L'],
  'mouthRight': ['A32_Mouth_Right', 'Mouth_R'],
  'mouthRollUpper': ['A33_Mouth_Roll_Upper', 'Mouth_Roll_In_Upper_L', 'Mouth_Roll_In_Upper_R', 'Mouth_Top_Lip_Under'],
  'mouthRollLower': ['A34_Mouth_Roll_Lower', 'Mouth_Roll_In_Lower_L', 'Mouth_Roll_In_Lower_R', 'Mouth_Bottom_Lip_Under'],
  'mouthShrugUpper': ['A35_Mouth_Shrug_Upper', 'Mouth_Shrug_Upper', 'Mouth_Top_Lip_Up', 'Mouth_Up'],
  'mouthShrugLower': ['A36_Mouth_Shrug_Lower', 'Mouth_Shrug_Lower', 'Mouth_Bottom_Lip_Down', 'Mouth_Down'],
  'mouthClose': ['A37_Mouth_Close', 'Mouth_Close', 'Mouth_Lips_Tight'],
  'mouthSmileLeft': ['A38_Mouth_Smile_Left', 'Mouth_Smile_L'],
  'mouthSmileRight': ['A39_Mouth_Smile_Right', 'Mouth_Smile_R'],
  'mouthFrownLeft': ['A40_Mouth_Frown_Left', 'Mouth_Frown_L'],
  'mouthFrownRight': ['A41_Mouth_Frown_Right', 'Mouth_Frown_R'],
  'mouthDimpleLeft': ['A42_Mouth_Dimple_Left', 'Mouth_Dimple_L'],
  'mouthDimpleRight': ['A43_Mouth_Dimple_Right', 'Mouth_Dimple_R'],
  'mouthUpperUpLeft': ['A44_Mouth_Upper_Up_Left', 'Mouth_Up_Upper_L', 'Mouth_Snarl_Upper_L'],
  'mouthUpperUpRight': ['A45_Mouth_Upper_Up_Right', 'Mouth_Up_Upper_R', 'Mouth_Snarl_Upper_R'],
  'mouthLowerDownLeft': ['A46_Mouth_Lower_Down_Left', 'Mouth_Down_Lower_L', 'Mouth_Snarl_Lower_L'],
  'mouthLowerDownRight': ['A47_Mouth_Lower_Down_Right', 'Mouth_Down_Lower_R', 'Mouth_Snarl_Lower_R'],
  'mouthPressLeft': ['A48_Mouth_Press_Left', 'Mouth_Press_L'],
  'mouthPressRight': ['A49_Mouth_Press_Right', 'Mouth_Press_R'],
  'mouthStretchLeft': ['A50_Mouth_Stretch_Left', 'Mouth_Stretch_L', 'Mouth_Widen_Sides'],
  'mouthStretchRight': ['A51_Mouth_Stretch_Right', 'Mouth_Stretch_R', 'Mouth_Widen_Sides'],

  // Tongue
  'tongueOut': ['A52_Tongue_Out', 'Tongue_Bulge_L', 'Tongue_Out', 'V_Tongue_Out']
};

// Helper function to find the first matching morph target name in the dictionary
function findMorphTarget(key, morphTargetDictionary) {
  const possibleNames = arkitToCCBaseMapping[key];
  if (!possibleNames) return null;

  for (const name of possibleNames) {
    if (name in morphTargetDictionary) {
      return name;
    }
  }
  return null;
}

function createAnimation (recordedData, morphTargetDictionary, bodyPart) {

  // console.log("Creating animation for:", bodyPart);
  // console.log("Available morph targets:", Object.keys(morphTargetDictionary));

  if (recordedData.length != 0) {
    let animation = []
    for (let i = 0; i < Object.keys(morphTargetDictionary).length; i++) {
      animation.push([])
    }
    let time = []
    let finishedFrames = 0

    // Track which morph targets we successfully mapped
    let mappedTargets = new Set();

    // Log unmapped blendshapes for debugging
    let unmappedKeys = new Set();

    recordedData.forEach((d, i) => {
        Object.entries(d.blendshapes).forEach(([key, value]) => {
          // Find the matching morph target name
          const morphTargetName = findMorphTarget(key, morphTargetDictionary);

          if (!morphTargetName) {
            if (i === 0) unmappedKeys.add(key);
            return;
          }

          // if (key == 'mouthShrugUpper') {
          //   value += 0.4;
          // }

          mappedTargets.add(key + ' -> ' + morphTargetName);
          animation[morphTargetDictionary[morphTargetName]].push(value)
        });
        time.push(finishedFrames / fps)
        finishedFrames++

    })

    // console.log("Mapped blendshapes:", Array.from(mappedTargets));
    // console.log("Unmapped blendshapes:", Array.from(unmappedKeys));

    let tracks = []

    //create morph animation
    Object.entries(recordedData[0].blendshapes).forEach(([key, value]) => {
      const morphTargetName = findMorphTarget(key, morphTargetDictionary);

      if (!morphTargetName) {
        return;
      }

      let i = morphTargetDictionary[morphTargetName]

      let track = new NumberKeyframeTrack(`${bodyPart}.morphTargetInfluences[${i}]`, time, animation[i])
      tracks.push(track)
    });

    // console.log("Created", tracks.length, "animation tracks");

    const clip = new AnimationClip('animation', -1, tracks);
    return clip
  }
  return null
}

export default createAnimation;