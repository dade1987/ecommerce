import _ from 'lodash';

// Import bone mapping and offsets from JSON (edit the JSON to change mappings/offsets)
import ryiaBoneMapping from './3Dmodels/Ryia/boneMapping.json';

// Import quaternion utilities for applying rotation offsets
import { applyOffsetToTrack } from './quaternionUtils';

// Mixamo to CC_Base bone name mapping - caricato da JSON
// Per modificare: editare resources/js/avatar3d/3Dmodels/Ryia/boneMapping.json
export const MIXAMO_TO_CC_BONE_MAP = ryiaBoneMapping.mapping;

// Offsets per correggere rotazioni bones (in gradi Euler)
// Permette di aggiustare animazioni Mixamo per adattarle al rig CC_Base
// Valori definiti nel JSON: { x: gradi, y: gradi, z: gradi }
export const BONE_OFFSETS = ryiaBoneMapping.offsets || {};

// Filter animation tracks to include only specific bones
// Escludi braccia/mani/dita - le gestiamo con armAnimation.js
// Le gambe/piedi ora sono gestiti con offset dal JSON (vedi boneMapping.json)
export function filterAnimationTracks(clips) {
  clips[0].tracks = _.filter(clips[0].tracks, track => {
    // Only include rotation tracks (quaternion), not position
    if (!track.name.includes("quaternion")) {
      return false;
    }
    // Escludi braccia/spalle - gestite da armAnimation.js
    // NOTA: Hand e dita (Thumb, Index, Middle, Ring, Pinky) ora incluse nell'animazione
    if (track.name.includes("Shoulder") ||
        track.name.includes("Arm") ||
        // track.name.includes("Hand") ||
        // track.name.includes("Thumb") ||
        track.name.includes("Thumb") ||    // pollice
        track.name.includes("Index") ||    // indice
        track.name.includes("Middle") ||   // medio
        track.name.includes("Ring") ||     // anulare
        track.name.includes("Pinky") ||    // mignolo
        track.name.includes("Toe") ||        // dita piede
        track.name.includes("Pinky")) {
      return false;
    }
    // Includi: spine, testa, collo, GAMBE COMPLETE (coscia, polpaccio, piede, dita)
    // NOTA: Hips escluso - causa rotazione 90° (orientamento diverso Mixamo vs CC_Base)
    // Le rotazioni delle gambe possono essere corrette con offset nel JSON
    return  track.name.includes("Spine") ||
            track.name.includes("Head") ||
            track.name.includes("Neck") ||
            // track.name.includes("Index") ||
            //track.name.includes("UpLeg") ||    // coscia (thigh)
            //track.name.includes("Leg") ||      // polpaccio (calf)
            track.name.includes("Foot") ||
            track.name.includes("Hand")

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
// Applica anche gli offset definiti in BONE_OFFSETS per correggere rotazioni
export function mapAnimationTracks(clips, boneMap = MIXAMO_TO_CC_BONE_MAP, offsets = BONE_OFFSETS) {
  clips[0].tracks = clips[0].tracks
    .map(track => {
      const [boneName, property] = track.name.split('.');
      if (boneMap[boneName]) {
        track.name = `${boneMap[boneName]}.${property}`;

        // Adjust quaternion for shoulders (Clavicle) - legacy method
        if (boneName.includes('Shoulder') && property === 'quaternion') {
          for (let i = 0; i < track.values.length; i += 4) {
            track.values[i] *= SHOULDER_QUAT_ADJUST.x;
            track.values[i + 1] *= SHOULDER_QUAT_ADJUST.y;
            track.values[i + 2] *= SHOULDER_QUAT_ADJUST.z;
            track.values[i + 3] *= SHOULDER_QUAT_ADJUST.w;
          }
        }

        // ============================================================
        // APPLICA OFFSET DA JSON (nuovo sistema più flessibile)
        // ============================================================
        // Se questo bone ha un offset definito nel JSON, applicalo
        // Gli offset sono in gradi Euler e vengono convertiti in quaternion
        // Vedi quaternionUtils.js per la spiegazione matematica
        if (property === 'quaternion' && offsets[boneName]) {
          const offset = offsets[boneName];
          // Salta se l'offset è tutto a zero (o se è un commento)
          if (typeof offset === 'object' && (offset.x !== 0 || offset.y !== 0 || offset.z !== 0)) {
            track.values = applyOffsetToTrack(track.values, offset);
            // console.log(`Applied offset to ${boneName}:`, offset);
          }
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
