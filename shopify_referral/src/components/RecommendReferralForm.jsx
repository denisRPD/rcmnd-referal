import { useCallback, useEffect, useState } from "react";
import { useAppBridge } from "@shopify/app-bridge-react";
import { userLoggedInFetch } from "../App.jsx";
import {
  Button,
  Checkbox,
  Form,
  FormLayout,
  TextField,
} from "@shopify/polaris";

export const RecommendReferralForm = () => {
  const app = useAppBridge();
  const fetch = userLoggedInFetch(app);

  const [apiToken, setApiToken] = useState("");
  const [testUrl, setTestUrl] = useState("");
  const [referralActive, setReferralActive] = useState(false);

  const loadReferralSettings = async () => {
    const settings = await fetch("/referral-settings").then((response) =>
      response.json()
    );
    updateSettings(settings);
  };

  useEffect(() => {
    loadReferralSettings();
  }, []);

  const updateSettings = (settings) => {
    setApiToken(settings.apiToken);
    setTestUrl(settings.testUrl);
    setReferralActive(settings.isActive);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!apiToken || !testUrl) {
      console.debug("Validation error");
      return;
    }

    const settings = await fetch("/referral-settings", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        apiToken,
        testUrl,
        isActive: referralActive,
      }),
    }).then((res) => res.json());
    console.log("Settings", settings);
    updateSettings(settings);
  };

  const handleApiTokenChange = useCallback((value) => setApiToken(value), []);
  const handleTestUrlChange = useCallback((value) => setTestUrl(value), []);
  const handleReferralActiveChange = useCallback(
    (value) => setReferralActive(value),
    []
  );

  return (
    <>
      <Form onSubmit={handleSubmit}>
        <FormLayout>
          <TextField
            value={apiToken}
            onChange={handleApiTokenChange}
            label="Api Token"
            type="text"
            autoComplete="text"
          />

          <TextField
            value={testUrl}
            onChange={handleTestUrlChange}
            label="Test URL"
            type="url"
            autoComplete="url"
          />

          <Checkbox
            label="Active"
            checked={referralActive}
            onChange={handleReferralActiveChange}
          />

          <Button submit>Save</Button>
        </FormLayout>
      </Form>
    </>
  );
};
