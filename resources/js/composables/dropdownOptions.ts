export const useDropdownOptions = () => {
  const transportTypes = [
    { id: 1, name: 'Freezing Microbas' },
    { id: 2, name: 'Freezing Bus' },
    { id: 3, name: 'Open Truck' },
  ]

  const shipmentTypes = [
    { id: 1, name: 'Local' },
    { id: 2, name: 'Foreign' },
  ]

  const batchOptions = [
    { value: 1, label: 'Batch A' },
    { value: 2, label: 'Batch B' },
    { value: 3, label: 'Batch C' },
  ]

  // Level options → stored as number, displayed as text
  const levelOptions = [
    { value: 1, label: 'Level 1' },
    { value: 2, label: 'Level 2' },
    { value: 3, label: 'Level 3' },
  ]

  return { transportTypes, shipmentTypes, batchOptions,
    levelOptions, }
}
