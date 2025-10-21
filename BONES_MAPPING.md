# EnjoyBones System - Completo Sistema di Mappatura Ossatura

## Overview

Il sistema `window.EnjoyBones` fornisce un accesso completo e gestibile a tutte le 67 ossa dello scheletro umanoid. Ogni osso è accessibile tramite nome preciso, senza necessità di regex o pattern matching.

## Accesso alle Ossa

### 1. Accesso Diretto tramite Getter

```javascript
// Accesso a qualsiasi osso tramite nome esatto
const headBone = window.EnjoyBones.Head;
const leftArm = window.EnjoyBones.LeftArm;
const rightHandThumb = window.EnjoyBones.RightHandThumb1;
const leftFootBone = window.EnjoyBones.LeftFoot;
```

### 2. Accesso Completo tramite Mappa

```javascript
// Ottieni la mappa completa di tutte le ossa
const allBones = window.EnjoyBones.bones;

// Itera su tutte le ossa
Object.entries(allBones).forEach(([boneName, boneObj]) => {
  console.log(`${boneName}:`, boneObj);
});
```

### 3. Verifica Disponibilità

```javascript
// Controlla se le ossa sono state caricate
if (window.EnjoyBones.ready) {
  console.log('Ossa caricate:', Object.keys(window.EnjoyBones.bones).length);
}
```

## Lista Completa Ossa Supportate

### Spine/Colonna Vertebrale
- Hips
- Spine
- Spine1
- Spine2
- Neck
- Head
- HeadTop_End

### Occhi
- LeftEye
- RightEye

### Braccia Sinistra
- LeftShoulder
- LeftArm
- LeftForeArm
- LeftHand

### Dita Sinistra - Pollice
- LeftHandThumb1, LeftHandThumb2, LeftHandThumb3, LeftHandThumb4

### Dita Sinistra - Indice
- LeftHandIndex1, LeftHandIndex2, LeftHandIndex3, LeftHandIndex4

### Dita Sinistra - Medio
- LeftHandMiddle1, LeftHandMiddle2, LeftHandMiddle3, LeftHandMiddle4

### Dita Sinistra - Anulare
- LeftHandRing1, LeftHandRing2, LeftHandRing3, LeftHandRing4

### Dita Sinistra - Mignolo
- LeftHandPinky1, LeftHandPinky2, LeftHandPinky3, LeftHandPinky4

### Braccia Destra
- RightShoulder
- RightArm
- RightForeArm
- RightHand

### Dita Destra - Pollice
- RightHandThumb1, RightHandThumb2, RightHandThumb3, RightHandThumb4

### Dita Destra - Indice
- RightHandIndex1, RightHandIndex2, RightHandIndex3, RightHandIndex4

### Dita Destra - Medio
- RightHandMiddle1, RightHandMiddle2, RightHandMiddle3, RightHandMiddle4

### Dita Destra - Anulare
- RightHandRing1, RightHandRing2, RightHandRing3, RightHandRing4

### Dita Destra - Mignolo
- RightHandPinky1, RightHandPinky2, RightHandPinky3, RightHandPinky4

### Gambe Sinistra
- LeftUpLeg
- LeftLeg
- LeftFoot
- LeftToeBase
- LeftToe_End

### Gambe Destra
- RightUpLeg
- RightLeg
- RightFoot
- RightToeBase
- RightToe_End

## Utilizzo Avanzato

### Animazione di Ossa

```javascript
// Anima una specifica osso
const armBone = window.EnjoyBones.LeftArm;
if (armBone) {
  armBone.rotation.x = Math.PI / 4; // 45 gradi
  armBone.updateMatrixWorld(true);
}
```

### Operazioni su Più Ossa

```javascript
// Ruota tutte le dita di una mano
const fingerBones = [
  window.EnjoyBones.LeftHandThumb1,
  window.EnjoyBones.LeftHandIndex1,
  window.EnjoyBones.LeftHandMiddle1,
  window.EnjoyBones.LeftHandRing1,
  window.EnjoyBones.LeftHandPinky1,
];

fingerBones.forEach(bone => {
  if (bone) bone.rotation.z += 0.1;
});
```

### Pose Predefinite

```javascript
// Applica una posa rilassata (braccia giù)
if (window.EnjoyBones.poseRelax) {
  window.EnjoyBones.poseRelax();
}
```

## Proprietà e Metodi

- `window.EnjoyBones.ready` - boolean: Indica se le ossa sono caricate
- `window.EnjoyBones.bones` - object: Mappa completa di tutte le ossa
- `window.EnjoyBones.poseRelax()` - function: Applica una posa rilassata
- Tutti i 67 nomi ossa come getter (es: `window.EnjoyBones.Head`)

## Note Importanti

1. **Nomi Precisi**: Non usare regex o pattern matching - usa i nomi esatti dalla lista
2. **Mapping Automatico**: Il sistema mappia automaticamente tutte le ossa durante il caricamento
3. **Compatibilità**: Tutte le ossa speciali (Head, Arms, Shoulders, etc.) vengono anche assegnate alle variabili globali per compatibilità
4. **Performance**: Il sistema utilizza lookup O(1) su una mappa, molto più efficiente delle regex

## Debug

```javascript
// Visualizza tutte le ossa caricate
console.log('Ossa disponibili:', window.EnjoyBones.bones);
console.log('Numero ossa:', Object.keys(window.EnjoyBones.bones).length);

// Verifica una specifica osso
const bone = window.EnjoyBones.LeftArm;
console.log(bone ? 'LeftArm trovato' : 'LeftArm non trovato');
```
