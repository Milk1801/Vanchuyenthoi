const currentUser = JSON.parse(localStorage.getItem('sincere_user') || '{}');

export const hasRole = (roleIdOrArray) => {
  if (!currentUser.ds_quyen) return false;
  const roles = currentUser.ds_quyen.map(q => Number(q.ma_quyen));
  if (roles.includes(5)) return true;
  
  const requiredRoles = Array.isArray(roleIdOrArray) ? roleIdOrArray : [roleIdOrArray];
  return requiredRoles.some(r => roles.includes(Number(r)));
};