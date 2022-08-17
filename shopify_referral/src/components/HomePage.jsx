import { Card, Heading, Layout, Page, TextContainer } from "@shopify/polaris";
import { RecommendReferralForm } from "./RecommendReferralForm";

export function HomePage() {
  return (
    <Page fullWidth>
      <Layout>
        <Layout.Section>
          <Layout.AnnotatedSection
            title="Recommend Referral"
            description="Go to the Recommend platform > My Organization
                    and select Integration from the left menu.
                    Then select API Keys and copy-paste values in the fields
                    below. Once you have the API key and URL in pace click save
                    to save settings."
          />
          <Card sectioned>
            <TextContainer spacing="loose">
              <Heading>Settings</Heading>
              <br />
            </TextContainer>
            <RecommendReferralForm />
          </Card>
        </Layout.Section>
      </Layout>
    </Page>
  );
}
