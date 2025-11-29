import React, { useEffect, useState, useMemo, useRef } from 'react'
import { useFrame } from '@react-three/fiber'
import { useGLTF, useFBX, useAnimations } from '@react-three/drei';
import { useControls, folder, Leva } from 'leva';
import * as THREE from 'three';
import axios from 'axios';
import _ from 'lodash';

import createAnimation from './converter';
import blinkData from './blendDataBlink.json';
import { useAvatarTextures } from './avatarTextures';
import {
  setupBodyMaterial,
  setupEyesMaterial,
  setupBrowsMaterial,
  setupTeethMaterial,
  setupHairMaterial,
  setupTshirtMaterial,
  setupShirtMaterial,
  setupSuitMaterial,
  hideClothingMesh,
} from './avatarMaterials';
import {
  filterAnimationTracks,
  mapAnimationTracks,
} from './avatarAnimation';
import {
  findBones,
} from './avatarBoneTracking';
import { createArmAnimationClip } from './armAnimation';
import { useMouseTracking } from './hooks/useMouseTracking';

// Base path for assets
const ASSETS_BASE = '/avatar3d';

function Avatar({
  avatarUrl = `${ASSETS_BASE}/models/avatar.glb`,
  speak,
  setSpeak,
  text,
  setAudioSource,
  playing,
  hideClothing = false,
  enableBoneControls = false,
  showLevaPanel = false,
  ttsEndpoint = '/api/avatar3d/tts'
}) {
  let gltf = useGLTF(avatarUrl);
  let morphTargetDictionaryBody = null;
  let morphTargetDictionaryLowerTeeth = null;
  let facialMeshes = [];

  // Load textures
  const textures = useAvatarTextures();

  // Setup meshes and materials
  gltf.scene.traverse(node => {
    if (node.type === 'Mesh' || node.type === 'LineSegments' || node.type === 'SkinnedMesh') {
      node.castShadow = true;
      node.receiveShadow = true;
      node.frustumCulled = false;

      // Body mesh
      if (node.name.includes("Body") || node.name === "CC_Base_Body_1") {
        setupBodyMaterial(node, textures);
        if (node.morphTargetDictionary && Object.keys(node.morphTargetDictionary).length > 0) {
          morphTargetDictionaryBody = node.morphTargetDictionary;
        }
      }

      // Find mesh with facial morph targets (ARKit blendshapes)
      if (node.morphTargetDictionary && Object.keys(node.morphTargetDictionary).some(key =>
        key.includes('Jaw') || key.includes('jaw') ||
        key.includes('Mouth') || key.includes('mouth') ||
        key.includes('Eye_Blink') || key.includes('eyeBlink')
      )) {
        facialMeshes.push({
          name: node.name,
          dictionary: node.morphTargetDictionary
        });
        if (!morphTargetDictionaryBody) {
          morphTargetDictionaryBody = node.morphTargetDictionary;
        }
      }

      // Other meshes
      if (node.name.includes("Eyes")) setupEyesMaterial(node, textures);
      if (node.name.includes("Brows")) setupBrowsMaterial(node);
      if (node.name.includes("Teeth")) setupTeethMaterial(node, textures);
      if (node.name.includes("Hair")) setupHairMaterial(node, textures);
      if (node.name.includes("TSHIRT")) setupTshirtMaterial(node, textures);
      if (node.name.includes("shirt") || node.name.includes("Shirt")) setupShirtMaterial(node);
      if (node.name.includes("Suit")) setupSuitMaterial(node);
      if (node.name.includes("TeethLower")) morphTargetDictionaryLowerTeeth = node.morphTargetDictionary;

      // Hide clothing if requested
      if (hideClothing) hideClothingMesh(node);
    }
  });

  const [clips, setClips] = useState([]);
  const mixer = useMemo(() => new THREE.AnimationMixer(gltf.scene), []);
  const bonesRef = useRef({});

  // Mouse tracking per testa e collo
  const { updateTracking } = useMouseTracking(gltf.scene, { head: true, neck: true, arms: false });

  // Leva controls per braccia - attivabile con enableBoneControls={true}
  const armControls = useControls('Braccia', {
    'Spine 01': folder({
      spine01X: { value: 0, min: -180, max: 180, step: 1 },
      spine01Y: { value: 8, min: -180, max: 180, step: 1 },
      spine01Z: { value: 0, min: -180, max: 180, step: 1 },
    }),
    'Spine 02': folder({
      spine02X: { value: 6, min: -180, max: 180, step: 1 },
      spine02Y: { value: -9, min: -180, max: 180, step: 1 },
      spine02Z: { value: 0, min: -180, max: 180, step: 1 },
    }),
    'RibsTwist SX': folder({
      lRibsX: { value: 132, min: -180, max: 180, step: 1 },
      lRibsY: { value: -180, min: -180, max: 180, step: 1 },
      lRibsZ: { value: 19, min: -180, max: 180, step: 1 },
    }),
    'RibsTwist DX': folder({
      rRibsX: { value: -56, min: -180, max: 180, step: 1 },
      rRibsY: { value: 6, min: -180, max: 180, step: 1 },
      rRibsZ: { value: 173, min: -180, max: 180, step: 1 },
    }),
    'Clavicola SX': folder({
      lClavX: { value: 0, min: -180, max: 180, step: 1 },
      lClavY: { value: 0, min: -180, max: 180, step: 1 },
      lClavZ: { value: -112, min: -180, max: 180, step: 1 },
    }),
    'Clavicola DX': folder({
      rClavX: { value: 0, min: -180, max: 180, step: 1 },
      rClavY: { value: 0, min: -180, max: 180, step: 1 },
      rClavZ: { value: 116, min: -180, max: 180, step: 1 },
    }),
    'Braccio SX': folder({
      lArmX: { value: 0, min: -180, max: 180, step: 1 },
      lArmY: { value: 0, min: -180, max: 180, step: 1 },
      lArmZ: { value: -55, min: -180, max: 180, step: 1 },
    }),
    'Braccio DX': folder({
      rArmX: { value: 0, min: -180, max: 180, step: 1 },
      rArmY: { value: 0, min: -180, max: 180, step: 1 },
      rArmZ: { value: 52, min: -180, max: 180, step: 1 },
    }),
    'Avambraccio SX': folder({
      lForeX: { value: 0, min: -180, max: 180, step: 1 },
      lForeY: { value: 0, min: -180, max: 180, step: 1 },
      lForeZ: { value: 9, min: -180, max: 180, step: 1 },
    }),
    'Avambraccio DX': folder({
      rForeX: { value: 0, min: -180, max: 180, step: 1 },
      rForeY: { value: 0, min: -180, max: 180, step: 1 },
      rForeZ: { value: -3, min: -180, max: 180, step: 1 },
    }),
  }, { collapsed: !enableBoneControls });

  // Find bones on mount
  useEffect(() => {
    bonesRef.current = findBones(gltf.scene);
    // Aggiungi altri bones utili
    gltf.scene.traverse(node => {
      if (node.isBone) {
        if (node.name === 'CC_Base_Waist') bonesRef.current.waist = node;
        if (node.name === 'CC_Base_Spine01') bonesRef.current.spine01 = node;
        if (node.name === 'CC_Base_Spine02') bonesRef.current.spine02 = node;
        if (node.name === 'CC_Base_L_RibsTwist') bonesRef.current.leftRibs = node;
        if (node.name === 'CC_Base_R_RibsTwist') bonesRef.current.rightRibs = node;
        if (node.name === 'CC_Base_L_Forearm') bonesRef.current.leftForeArm = node;
        if (node.name === 'CC_Base_R_Forearm') bonesRef.current.rightForeArm = node;
      }
    });
  }, [gltf.scene]);

  // Speech/lip-sync effect - usa ttsEndpoint di Laravel
  useEffect(() => {
    if (speak === false) return;

    axios.post(ttsEndpoint, { text })
      .then(response => {
        let { blendData, filename } = response.data;

        // Create animations for ALL facial meshes
        let newClips = [];
        facialMeshes.forEach(mesh => {
          const clip = createAnimation(blendData, mesh.dictionary, mesh.name);
          if (clip) newClips.push(clip);
        });

        setClips(newClips);
        setAudioSource(filename);
      })
      .catch(err => {
        console.error(err);
        setSpeak(false);
      });
  }, [speak]);

  // Load idle animation
  let idleFbx = useFBX(`${ASSETS_BASE}/models/standing-briefcase-idle.fbx`);
  let { clips: idleClips } = useAnimations(idleFbx.animations);

  // Filter and map animation tracks
  filterAnimationTracks(idleClips);
  mapAnimationTracks(idleClips);

  // Play idle and blink animations
  useEffect(() => {
    let idleClipAction = mixer.clipAction(idleClips[0]);
    idleClipAction.play();

    // Play custom arm animation
    const armClip = createArmAnimationClip();
    const armAction = mixer.clipAction(armClip);
    armAction.play();

    // Apply blink animation to all facial meshes
    facialMeshes.forEach(mesh => {
      let blinkClip = createAnimation(blinkData, mesh.dictionary, mesh.name);
      if (blinkClip) {
        let blinkAction = mixer.clipAction(blinkClip);
        blinkAction.play();
      }
    });
  }, []);

  // Play speech animation clips
  useEffect(() => {
    if (playing === false) return;

    _.each(clips, clip => {
      let clipAction = mixer.clipAction(clip);
      clipAction.setLoop(THREE.LoopOnce);
      clipAction.play();
    });
  }, [playing]);

  // Animation frame update
  useFrame((state, delta) => {
    mixer.update(delta);

    // Mouse tracking per testa/collo (dopo mixer.update per sovrascrivere)
    updateTracking();

    // Applica rotazioni da Leva controls solo se abilitato
    if (enableBoneControls) {
      const deg2rad = (deg) => deg * (Math.PI / 180);
      const bones = bonesRef.current;

      if (bones.spine01) {
        bones.spine01.rotation.x = deg2rad(armControls.spine01X);
        bones.spine01.rotation.y = deg2rad(armControls.spine01Y);
        bones.spine01.rotation.z = deg2rad(armControls.spine01Z);
      }
      if (bones.spine02) {
        bones.spine02.rotation.x = deg2rad(armControls.spine02X);
        bones.spine02.rotation.y = deg2rad(armControls.spine02Y);
        bones.spine02.rotation.z = deg2rad(armControls.spine02Z);
      }
      if (bones.leftRibs) {
        bones.leftRibs.rotation.x = deg2rad(armControls.lRibsX);
        bones.leftRibs.rotation.y = deg2rad(armControls.lRibsY);
        bones.leftRibs.rotation.z = deg2rad(armControls.lRibsZ);
      }
      if (bones.rightRibs) {
        bones.rightRibs.rotation.x = deg2rad(armControls.rRibsX);
        bones.rightRibs.rotation.y = deg2rad(armControls.rRibsY);
        bones.rightRibs.rotation.z = deg2rad(armControls.rRibsZ);
      }
      if (bones.leftClavicle) {
        bones.leftClavicle.rotation.x = deg2rad(armControls.lClavX);
        bones.leftClavicle.rotation.y = deg2rad(armControls.lClavY);
        bones.leftClavicle.rotation.z = deg2rad(armControls.lClavZ);
      }
      if (bones.rightClavicle) {
        bones.rightClavicle.rotation.x = deg2rad(armControls.rClavX);
        bones.rightClavicle.rotation.y = deg2rad(armControls.rClavY);
        bones.rightClavicle.rotation.z = deg2rad(armControls.rClavZ);
      }
      if (bones.leftUpperArm) {
        bones.leftUpperArm.rotation.x = deg2rad(armControls.lArmX);
        bones.leftUpperArm.rotation.y = deg2rad(armControls.lArmY);
        bones.leftUpperArm.rotation.z = deg2rad(armControls.lArmZ);
      }
      if (bones.rightUpperArm) {
        bones.rightUpperArm.rotation.x = deg2rad(armControls.rArmX);
        bones.rightUpperArm.rotation.y = deg2rad(armControls.rArmY);
        bones.rightUpperArm.rotation.z = deg2rad(armControls.rArmZ);
      }
      if (bones.leftForeArm) {
        bones.leftForeArm.rotation.x = deg2rad(armControls.lForeX);
        bones.leftForeArm.rotation.y = deg2rad(armControls.lForeY);
        bones.leftForeArm.rotation.z = deg2rad(armControls.lForeZ);
      }
      if (bones.rightForeArm) {
        bones.rightForeArm.rotation.x = deg2rad(armControls.rForeX);
        bones.rightForeArm.rotation.y = deg2rad(armControls.rForeY);
        bones.rightForeArm.rotation.z = deg2rad(armControls.rForeZ);
      }
    }
  });

  return (
    <group name="avatar">
      <primitive object={gltf.scene} dispose={null} />
    </group>
  );
}

export { Leva };
export default Avatar;
