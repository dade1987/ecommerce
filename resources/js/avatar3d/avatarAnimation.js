import _ from 'lodash';

// Mixamo to CC_Base bone name mapping
export const MIXAMO_TO_CC_BONE_MAP = {
  'mixamorigHips': 'CC_Base_Hip',
  'mixamorigSpine': 'CC_Base_Waist',
  'mixamorigSpine1': 'CC_Base_Spine01',
  'mixamorigSpine2': 'CC_Base_Spine02',
  'mixamorigNeck': 'CC_Base_NeckTwist01',
  'mixamorigHead': 'CC_Base_Head',
  'mixamorigLeftShoulder': 'CC_Base_L_Clavicle',
  'mixamorigLeftArm': 'CC_Base_L_Upperarm',
  'mixamorigLeftForeArm': 'CC_Base_L_Forearm',
  'mixamorigLeftHand': 'CC_Base_L_Hand',
  'mixamorigRightShoulder': 'CC_Base_R_Clavicle',
  'mixamorigRightArm': 'CC_Base_R_Upperarm',
  'mixamorigRightForeArm': 'CC_Base_R_Forearm',
  'mixamorigRightHand': 'CC_Base_R_Hand',
};

// Filter animation tracks to include only specific bones
// Escludi braccia - le gestiamo con armAnimation.js
export function filterAnimationTracks(clips) {
  clips[0].tracks = _.filter(clips[0].tracks, track => {
    // Only include rotation tracks (quaternion), not position
    if (!track.name.includes("quaternion")) {
      return false;
    }
    // Escludi braccia/mani/spalle - gestite da armAnimation.js
    if (track.name.includes("Shoulder") ||
        track.name.includes("Arm") ||
        track.name.includes("Hand")) {
      return false;
    }
    return track.name.includes("Spine") ||
           track.name.includes("Head") ||
           track.name.includes("Neck");
  });
  return clips;
}

// Shoulder quaternion adjustment - modify these values to experiment
export const SHOULDER_QUAT_ADJUST = {
  x: -1,  // 1 = normal, -1 = inverted
  y: -1,  // 1 = normal, -1 = inverted
  z: -1,  // 1 = normal, -1 = inverted
  w: -1,  // 1 = normal, -1 = inverted
};

// Map Mixamo bone names to CC_Base bone names in animation tracks
export function mapAnimationTracks(clips, boneMap = MIXAMO_TO_CC_BONE_MAP) {
  clips[0].tracks = clips[0].tracks
    .map(track => {
      const [boneName, property] = track.name.split('.');
      if (boneMap[boneName]) {
        track.name = `${boneMap[boneName]}.${property}`;

        // Adjust quaternion for shoulders (Clavicle)
        if (boneName.includes('Shoulder') && property === 'quaternion') {
          for (let i = 0; i < track.values.length; i += 4) {
            track.values[i] *= SHOULDER_QUAT_ADJUST.x;
            track.values[i + 1] *= SHOULDER_QUAT_ADJUST.y;
            track.values[i + 2] *= SHOULDER_QUAT_ADJUST.z;
            track.values[i + 3] *= SHOULDER_QUAT_ADJUST.w;
          }
          // console.log('Adjusted quaternion for:', boneName, SHOULDER_QUAT_ADJUST);
        }

        return track;
      }
      return null; // Remove tracks without mapping
    })
    .filter(track => track !== null);
  return clips;
}

// Log animation tracks for debugging
export function logAnimationTracks(animations, label = "Animation Tracks") {
  console.log(`=== ${label} ===`);
  animations[0].tracks.forEach(track => {
    console.log("Track:", track.name);
  });
  console.log(`=== End ${label} ===`);
}