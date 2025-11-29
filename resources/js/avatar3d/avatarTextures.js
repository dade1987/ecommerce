import { useTexture } from '@react-three/drei';
import { SRGBColorSpace, LinearSRGBColorSpace } from 'three';
import _ from 'lodash';

// Base path for assets
const ASSETS_BASE = '/avatar3d';

// Texture paths configuration
const TEXTURE_PATHS = [
  `${ASSETS_BASE}/images/body.webp`,
  `${ASSETS_BASE}/images/eyes.webp`,
  `${ASSETS_BASE}/images/teeth_diffuse.webp`,
  `${ASSETS_BASE}/images/body_specular.webp`,
  `${ASSETS_BASE}/images/body_roughness.webp`,
  `${ASSETS_BASE}/images/body_normal.webp`,
  `${ASSETS_BASE}/images/teeth_normal.webp`,
  `${ASSETS_BASE}/images/h_color.webp`,
  `${ASSETS_BASE}/images/tshirt_diffuse.webp`,
  `${ASSETS_BASE}/images/tshirt_normal.webp`,
  `${ASSETS_BASE}/images/tshirt_roughness.webp`,
  `${ASSETS_BASE}/images/h_alpha.webp`,
  `${ASSETS_BASE}/images/h_normal.webp`,
  `${ASSETS_BASE}/images/h_roughness.webp`,
];

export function useAvatarTextures() {
  const [
    bodyTexture,
    eyesTexture,
    teethTexture,
    bodySpecularTexture,
    bodyRoughnessTexture,
    bodyNormalTexture,
    teethNormalTexture,
    hairTexture,
    tshirtDiffuseTexture,
    tshirtNormalTexture,
    tshirtRoughnessTexture,
    hairAlphaTexture,
    hairNormalTexture,
    hairRoughnessTexture,
  ] = useTexture(TEXTURE_PATHS);

  // Apply sRGB colorspace and flipY to most textures
  _.each([
    bodyTexture,
    eyesTexture,
    teethTexture,
    teethNormalTexture,
    bodySpecularTexture,
    bodyRoughnessTexture,
    bodyNormalTexture,
    tshirtDiffuseTexture,
    tshirtNormalTexture,
    tshirtRoughnessTexture,
    hairAlphaTexture,
    hairNormalTexture,
    hairRoughnessTexture
  ], t => {
    t.colorSpace = SRGBColorSpace;
    t.flipY = false;
  });

  // Normal maps use Linear colorspace
  bodyNormalTexture.colorSpace = LinearSRGBColorSpace;
  tshirtNormalTexture.colorSpace = LinearSRGBColorSpace;
  teethNormalTexture.colorSpace = LinearSRGBColorSpace;
  hairNormalTexture.colorSpace = LinearSRGBColorSpace;

  return {
    bodyTexture,
    eyesTexture,
    teethTexture,
    bodySpecularTexture,
    bodyRoughnessTexture,
    bodyNormalTexture,
    teethNormalTexture,
    hairTexture,
    tshirtDiffuseTexture,
    tshirtNormalTexture,
    tshirtRoughnessTexture,
    hairAlphaTexture,
    hairNormalTexture,
    hairRoughnessTexture,
  };
}