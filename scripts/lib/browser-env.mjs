// Endpoints for the browser validators. Nothing is hard-coded here: the DevTools
// port and the site origin come from the environment (DT-T09). Missing values stop the
// validator before it connects, so a run never silently targets the wrong browser or host.
const required = (name) => {
  const value = process.env[name];
  if (!value) {
    throw new Error(`Defina ${name} antes de rodar este validador (ver docs/operations/DEBT-REGISTER.md, DT-T09).`);
  }
  return value;
};

export const devtoolsUrl = (path) => `http://127.0.0.1:${required('ACULTA_DEVTOOLS_PORT')}${path}`;
export const siteOrigin = () => required('ACULTA_SITE_ORIGIN').replace(/\/$/, '');
export const siteUrl = (path = '/') => `${siteOrigin()}${path}`;
