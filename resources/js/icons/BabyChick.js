// icons/BabyChick.js
import { createLucideIcon } from 'lucide-vue-next'

export const BabyChick = createLucideIcon("BabyChick", [
  // Head
  ["circle", { cx: "8", cy: "8", r: "3", key: "head" }],
  // Body
  ["ellipse", { cx: "13", cy: "13.5", rx: "6.5", ry: "5", key: "body" }],
  // Eye
  ["circle", { cx: "8.9", cy: "7.6", r: "0.6", key: "eye" }],
  // Beak
  ["path", { d: "M10.7 8.2l1.6-.7-1.6-.7z", key: "beak" }],
  // Wing
  ["path", { d: "M10.5 13.2c1.2-.8 2.6-.8 3.8 0", key: "wing" }],
  // Tail feathers
  ["path", { d: "M18.8 12l1.6-1.2M18.6 13.4l1.9.5", key: "tail" }],
  // Legs
  ["path", { d: "M11 18.5v2.2M15 18.5v2.2", key: "legs" }],
  // Feet
  ["path", { d: "M10.1 20.7h1.8M14.1 20.7h1.8", key: "feet" }]
])
