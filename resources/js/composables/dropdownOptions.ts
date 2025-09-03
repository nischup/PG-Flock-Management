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

  return { transportTypes, shipmentTypes }
}
