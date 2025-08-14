// resources/js/Composables/useNotifier.ts
import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';

type AlertIcon = 'success' | 'info' | 'error' | 'warning' | 'question';

export function useNotifier() {
  const showAlert = ({
    title = '',
    text = '',
    icon = 'info',
    confirmButtonText = 'OK',
  }: {
    title?: string;
    text?: string;
    icon?: AlertIcon;
    confirmButtonText?: string;
  }) => {
    Swal.fire({
      title,
      text,
      icon,
      confirmButtonText,
    });
  };

  const showSuccess = (message: string, title = 'Success') => {
    showAlert({ title, text: message, icon: 'success' });
  };

  const showInfo = (message: string, title = 'Info') => {
    showAlert({ title, text: message, icon: 'info' });
  };

  const showError = (message: string, title = 'Error') => {
    showAlert({ title, text: message, icon: 'error' });
  };

  const confirmDelete = ({
    url,
    title = 'Are you sure?',
    text = 'This will permanently delete the item.',
    confirmButtonText = 'Yes, delete it!',
    successMessage = 'Deleted successfully.',
    errorMessage = 'Something went wrong.',
    onSuccess,
    onError,
  }: {
    url: string;
    title?: string;
    text?: string;
    confirmButtonText?: string;
    successMessage?: string;
    errorMessage?: string;
    onSuccess?: () => void;
    onError?: () => void;
  }) => {
    Swal.fire({
      title,
      text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText,
    }).then((result) => {
      if (result.isConfirmed) {
        router.delete(url, {
          onSuccess: () => {
            showSuccess(successMessage, 'Deleted!');
            onSuccess?.();
          },
          onError: () => {
            showError(errorMessage, 'Error');
            onError?.();
          },
        });
      }
    });
  };

  return {
    showAlert,
    showSuccess,
    showInfo,
    showError,
    confirmDelete,
  };
}