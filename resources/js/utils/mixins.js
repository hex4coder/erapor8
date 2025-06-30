const userData = useCookie('userData')
const sekolah = useCookie("sekolah");
const semester = useCookie("semester");
const roles = useCookie("roles");
export const $user = userData.value
export const $sekolah = sekolah.value
export const $semester = semester.value
export const $roles = roles.value
