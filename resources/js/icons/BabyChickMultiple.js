// icons/BabyChickMultiple.js
import { createLucideIcon } from 'lucide-vue-next'

export const BabyChickMultiple = createLucideIcon("BabyChickMultiple", [
  // First chick (left)
  ["circle", { cx: "6", cy: "10", r: "2.5", key: "head1" }],
  ["ellipse", { cx: "9", cy: "14", rx: "4", ry: "3", key: "body1" }],
  ["circle", { cx: "6.3", cy: "9.8", r: "0.5", key: "eye1" }],
  ["path", { d: "M7 10l0.8-0.4-0.8-0.4z", key: "beak1" }],

  // Second chick (middle)
  ["circle", { cx: "12", cy: "9", r: "2.5", key: "head2" }],
  ["ellipse", { cx: "15", cy: "13", rx: "4", ry: "3", key: "body2" }],
  ["circle", { cx: "12.3", cy: "8.8", r: "0.5", key: "eye2" }],
  ["path", { d: "M13 9l0.8-0.4-0.8-0.4z", key: "beak2" }],

  // Third chick (right)
  ["circle", { cx: "18", cy: "10", r: "2.5", key: "head3" }],
  ["ellipse", { cx: "21", cy: "14", rx: "4", ry: "3", key: "body3" }],
  ["circle", { cx: "18.3", cy: "9.8", r: "0.5", key: "eye3" }],
  ["path", { d: "M19 10l0.8-0.4-0.8-0.4z", key: "beak3" }]
])
