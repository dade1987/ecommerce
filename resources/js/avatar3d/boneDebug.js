/**
 * Debug utilities per bones del modello 3D e animazioni
 * Usare questi metodi per:
 * 1. Estrarre tutte le bones dal modello GLB
 * 2. Estrarre tutte le bones richieste dall'animazione FBX
 * 3. Fare il match manuale e salvare in JSON
 */

/**
 * Estrae tutte le bones dal modello 3D e le stampa in console
 * @param {THREE.Object3D} scene - La scena del modello (gltf.scene)
 * @param {string} modelName - Nome del modello per il log
 * @returns {object} Oggetto con tutte le bones
 */
export function extractModelBones(scene, modelName = 'Model') {
  const bones = {
    modelName,
    extractedAt: new Date().toISOString(),
    bones: [],
    bonesByType: {
      head: [],
      spine: [],
      arm: [],
      hand: [],
      leg: [],
      foot: [],
      other: []
    }
  };

  scene.traverse(node => {
    if (node.isBone) {
      const boneName = node.name;
      bones.bones.push(boneName);

      // Categorizza le bones
      const lowerName = boneName.toLowerCase();
      if (lowerName.includes('head') || lowerName.includes('neck') || lowerName.includes('eye') || lowerName.includes('jaw')) {
        bones.bonesByType.head.push(boneName);
      } else if (lowerName.includes('spine') || lowerName.includes('waist') || lowerName.includes('hip') || lowerName.includes('pelvis')) {
        bones.bonesByType.spine.push(boneName);
      } else if (lowerName.includes('arm') || lowerName.includes('shoulder') || lowerName.includes('clavicle') || lowerName.includes('upperarm') || lowerName.includes('forearm')) {
        bones.bonesByType.arm.push(boneName);
      } else if (lowerName.includes('hand') || lowerName.includes('finger') || lowerName.includes('thumb')) {
        bones.bonesByType.hand.push(boneName);
      } else if (lowerName.includes('leg') || lowerName.includes('thigh') || lowerName.includes('calf') || lowerName.includes('knee')) {
        bones.bonesByType.leg.push(boneName);
      } else if (lowerName.includes('foot') || lowerName.includes('toe') || lowerName.includes('ankle')) {
        bones.bonesByType.foot.push(boneName);
      } else {
        bones.bonesByType.other.push(boneName);
      }
    }
  });

  // Ordina alfabeticamente
  bones.bones.sort();
  Object.keys(bones.bonesByType).forEach(key => {
    bones.bonesByType[key].sort();
  });

  console.log(`\n========== MODEL BONES: ${modelName} ==========`);
  console.log('Total bones:', bones.bones.length);
  console.log('\n--- By Category ---');
  Object.entries(bones.bonesByType).forEach(([category, boneList]) => {
    if (boneList.length > 0) {
      console.log(`\n[${category.toUpperCase()}] (${boneList.length}):`);
      boneList.forEach(b => console.log(`  - ${b}`));
    }
  });
  console.log('\n--- JSON (copy this) ---');
  console.log(JSON.stringify(bones, null, 2));
  console.log('========== END MODEL BONES ==========\n');

  return bones;
}

/**
 * Estrae tutte le bones richieste dall'animazione FBX
 * NOTA: Lavora sui dati senza modificarli
 * @param {THREE.AnimationClip[]} animations - Array di AnimationClip
 * @param {string} animationName - Nome dell'animazione per il log
 * @returns {object} Oggetto con tutte le bones dell'animazione
 */
export function extractAnimationBones(animations, animationName = 'Animation') {
  const animBones = {
    animationName,
    extractedAt: new Date().toISOString(),
    bones: [],
    tracks: []
  };

  if (!animations || animations.length === 0) {
    console.warn('No animations provided');
    return animBones;
  }

  // Estrai da animations (può essere già processato o no)
  animations.forEach((clip) => {
    clip.tracks.forEach(track => {
      const [boneName, property] = track.name.split('.');

      if (!animBones.bones.includes(boneName)) {
        animBones.bones.push(boneName);
      }

      animBones.tracks.push({
        boneName,
        property,
        fullName: track.name,
        valueCount: track.values.length
      });
    });
  });

  animBones.bones.sort();

  console.log(`\n========== ANIMATION BONES: ${animationName} ==========`);
  console.log('Total unique bones:', animBones.bones.length);
  console.log('\n--- Bones List ---');
  animBones.bones.forEach(b => console.log(`  - ${b}`));
  console.log('\n--- Tracks Detail ---');
  animBones.tracks.forEach(t => console.log(`  ${t.fullName} (${t.valueCount} values)`));
  console.log('\n--- JSON (copy this) ---');
  console.log(JSON.stringify(animBones, null, 2));
  console.log('========== END ANIMATION BONES ==========\n');

  return animBones;
}

/**
 * Estrae le bones ORIGINALI dall'FBX prima di qualsiasi mapping
 * Usa questo per vedere i nomi Mixamo originali
 * @param {THREE.Group} fbx - L'FBX caricato (non le clips processate)
 * @param {string} animationName - Nome per il log
 */
export function extractOriginalFbxBones(fbx, animationName = 'FBX Animation') {
  const animBones = {
    animationName,
    extractedAt: new Date().toISOString(),
    bones: [],
    tracks: []
  };

  // fbx.animations contiene i clip originali
  const animations = fbx.animations || [];

  if (animations.length === 0) {
    console.warn('No animations in FBX');
    return animBones;
  }

  animations.forEach((clip) => {
    clip.tracks.forEach(track => {
      const [boneName, property] = track.name.split('.');

      if (!animBones.bones.includes(boneName)) {
        animBones.bones.push(boneName);
      }

      animBones.tracks.push({
        boneName,
        property,
        fullName: track.name,
        valueCount: track.values.length
      });
    });
  });

  animBones.bones.sort();

  console.log(`\n========== ORIGINAL FBX BONES: ${animationName} ==========`);
  console.log('Total unique bones:', animBones.bones.length);
  console.log('\n--- Original Mixamo Bones ---');
  animBones.bones.forEach(b => console.log(`  - ${b}`));
  console.log('\n--- JSON (copy this) ---');
  console.log(JSON.stringify(animBones, null, 2));
  console.log('========== END ORIGINAL FBX BONES ==========\n');

  return animBones;
}

/**
 * Compara le bones del modello con quelle dell'animazione
 * Utile per trovare i match mancanti
 * @param {object} modelBones - Output di extractModelBones
 * @param {object} animBones - Output di extractAnimationBones
 */
export function compareBones(modelBones, animBones) {
  console.log('\n========== BONES COMPARISON ==========');

  const modelSet = new Set(modelBones.bones);
  const animSet = new Set(animBones.bones);

  // Bones nell'animazione non presenti nel modello
  const missingInModel = animBones.bones.filter(b => !modelSet.has(b));

  // Bones nel modello non usate dall'animazione
  const unusedInModel = modelBones.bones.filter(b => !animSet.has(b));

  console.log('\n--- Animation bones NOT in model (need mapping) ---');
  missingInModel.forEach(b => console.log(`  ❌ ${b}`));

  console.log('\n--- Model bones NOT used by animation ---');
  unusedInModel.forEach(b => console.log(`  ⚪ ${b}`));

  // Suggerisci mapping
  console.log('\n--- Suggested Mapping (manual review needed) ---');
  missingInModel.forEach(animBone => {
    const lowerAnimBone = animBone.toLowerCase().replace('mixamorig', '');
    const suggestions = modelBones.bones.filter(modelBone => {
      const lowerModelBone = modelBone.toLowerCase();
      return lowerModelBone.includes(lowerAnimBone) ||
             lowerAnimBone.includes(lowerModelBone.replace('cc_base_', '').replace('_', ''));
    });
    if (suggestions.length > 0) {
      console.log(`  "${animBone}": "${suggestions[0]}", // suggestions: ${suggestions.join(', ')}`);
    } else {
      console.log(`  "${animBone}": "???", // NO MATCH FOUND`);
    }
  });

  console.log('========== END COMPARISON ==========\n');
}

/**
 * Helper: chiama questo in Avatar.jsx per debug completo
 *
 * Esempio d'uso in Avatar.jsx:
 *
 * import { debugAllBones } from './boneDebug';
 *
 * // Dentro useEffect dopo che gltf e fbx sono caricati:
 * useEffect(() => {
 *   debugAllBones(gltf.scene, idleFbx.animations, 'Ryia', 'standing-briefcase-idle');
 * }, []);
 */
export function debugAllBones(modelScene, animations, modelName = 'Model', animName = 'Animation') {
  const modelBones = extractModelBones(modelScene, modelName);
  const animBones = extractAnimationBones(animations, animName);
  compareBones(modelBones, animBones);

  return { modelBones, animBones };
}