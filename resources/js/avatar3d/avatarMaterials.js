import { MeshStandardMaterial } from 'three/src/materials/MeshStandardMaterial';
import { LineBasicMaterial, MeshPhysicalMaterial, Vector2 } from 'three';
import * as THREE from 'three';

// Configure body mesh material
export function setupBodyMaterial(node, textures) {
  const { bodyTexture, bodyRoughnessTexture, bodyNormalTexture } = textures;

  node.castShadow = true;
  node.receiveShadow = true;

  // Only apply custom materials for original model, not CC_Base
  if (!node.name.startsWith("CC_Base")) {
    node.material = new MeshPhysicalMaterial();
    node.material.map = bodyTexture;
    node.material.roughness = 1.7;
    node.material.roughnessMap = bodyRoughnessTexture;
    node.material.normalMap = bodyNormalTexture;
    node.material.normalScale = new Vector2(0.6, 0.6);
    node.material.envMapIntensity = 0.8;
  }

  // Body reads stencil - only render where clothing hasn't written
  node.renderOrder = 0;
  node.material.stencilWrite = false;
  node.material.stencilRef = 1;
  node.material.stencilFunc = THREE.NotEqualStencilFunc;
}

// Configure eyes mesh material
export function setupEyesMaterial(node, textures) {
  const { eyesTexture } = textures;

  node.material = new MeshStandardMaterial();
  node.material.map = eyesTexture;
  node.material.roughness = 0.1;
  node.material.envMapIntensity = 0.5;
}

// Configure brows material
export function setupBrowsMaterial(node) {
  node.material = new LineBasicMaterial({ color: 0x000000 });
  node.material.linewidth = 1;
  node.material.opacity = 0.5;
  node.material.transparent = true;
  node.visible = false;
}

// Configure teeth mesh material
export function setupTeethMaterial(node, textures) {
  const { teethTexture, teethNormalTexture } = textures;

  node.receiveShadow = true;
  node.castShadow = true;
  node.material = new MeshStandardMaterial();
  node.material.roughness = 0.1;
  node.material.map = teethTexture;
  node.material.normalMap = teethNormalTexture;
  node.material.envMapIntensity = 0.7;
}

// Configure hair mesh material
export function setupHairMaterial(node, textures) {
  const { hairTexture, hairAlphaTexture, hairNormalTexture, hairRoughnessTexture } = textures;

  node.material = new MeshStandardMaterial();
  node.material.map = hairTexture;
  node.material.alphaMap = hairAlphaTexture;
  node.material.normalMap = hairNormalTexture;
  node.material.roughnessMap = hairRoughnessTexture;
  node.material.transparent = true;
  node.material.depthWrite = false;
  node.material.side = 2;
  node.material.color.setHex(0x000000);
  node.material.envMapIntensity = 0.3;
}

// Configure t-shirt mesh material
export function setupTshirtMaterial(node, textures) {
  const { tshirtDiffuseTexture, tshirtRoughnessTexture, tshirtNormalTexture } = textures;

  node.material = new MeshStandardMaterial();
  node.material.map = tshirtDiffuseTexture;
  node.material.roughnessMap = tshirtRoughnessTexture;
  node.material.normalMap = tshirtNormalTexture;
  node.material.color.setHex(0xffffff);
  node.material.envMapIntensity = 0.5;
}

// Configure shirt material (z-fighting fix)
export function setupShirtMaterial(node) {
  node.material.polygonOffset = true;
  node.material.polygonOffsetFactor = 4;
  node.material.polygonOffsetUnits = 4;
  node.renderOrder = 0;
}

// Configure suit material (stencil write)
export function setupSuitMaterial(node) {
  node.renderOrder = 1;
  node.material.stencilWrite = true;
  node.material.stencilRef = 1;
  node.material.stencilFunc = THREE.AlwaysStencilFunc;
  node.material.stencilZPass = THREE.ReplaceStencilOp;
}

// List of clothing mesh names to hide
const CLOTHING_NAMES = ["Suit", "shirt", "Shirt", "skirt", "Skirt", "Heels", "Shoes"];

// Hide clothing meshes
export function hideClothingMesh(node) {
  if (CLOTHING_NAMES.some(name => node.name.includes(name))) {
    node.visible = false;
  }
}
